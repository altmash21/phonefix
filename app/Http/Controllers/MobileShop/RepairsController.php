<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class RepairsController extends BaseMobileShopController
{
    /**
     * Repair Service Portal & Job Sheets
     */
    public function repairs(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-repairs') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('repair-technician')), 403, 'Unauthorized access to repair service desk.');

        $companyId = $this->getCompanyId();
        $query = DB::table('ms_repair_tickets')
            ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
            ->select('ms_repair_tickets.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
            ->where('ms_repair_tickets.company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('ms_repair_tickets.status', $request->status);
        }

        $tickets = $query->orderBy('ms_repair_tickets.id', 'desc')->get();

        $ticketIds = $tickets->pluck('id')->toArray();
        $ticketParts = DB::table('ms_repair_ticket_parts')
            ->join('ms_parts_inventory', 'ms_repair_ticket_parts.part_id', '=', 'ms_parts_inventory.id')
            ->whereIn('ms_repair_ticket_parts.repair_ticket_id', $ticketIds)
            ->select('ms_repair_ticket_parts.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category as part_category')
            ->get()
            ->groupBy('repair_ticket_id');

        foreach ($tickets as $t) {
            $t->decrypted_pin = $t->passcode_encrypted ? Crypt::decryptString($t->passcode_encrypted) : 'None';
            $t->decrypted_pattern = $t->pattern_code_encrypted ? Crypt::decryptString($t->pattern_code_encrypted) : 'None';
            $t->used_parts = $ticketParts->get($t->id, collect());
        }

        $parts = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $customers = DB::table('ms_customers')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $repairs = $tickets;

        return view('mobileshop.repairs', compact('tickets', 'repairs', 'parts', 'customers'));
    }

    /**
     * Store Repair Job Sheet
     */
    public function storeRepair(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-repairs') || auth()->user()->can('update-mobileshop-repairs') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('repair-technician')), 403, 'Unauthorized action.');

        $request->validate([
            'customer_phone' => 'required|string|min:7|max:20',
            'customer_name' => 'required|string|min:2|max:100',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'reported_faults' => 'required|string',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('phone', $request->customer_phone)->first();
            if (!$customer) {
                $customerId = DB::table('ms_customers')->insertGetId([
                    'company_id' => $companyId,
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $customerId = $customer->id;
            }

            // Atomic Sequential Ticket Number (e.g. REP-202608-0001)
            $ticketNo = $this->getNextInvoiceNumber($companyId, 'REP');
            $passcodeEnc = !empty($request->passcode) ? Crypt::encryptString($request->passcode) : null;
            $patternEnc = !empty($request->pattern_code) ? Crypt::encryptString($request->pattern_code) : null;

            $estCost = (float) ($request->estimated_cost ?? 0.00);
            $advPaid = (float) ($request->advance_paid ?? 0.00);

            DB::table('ms_repair_tickets')->insert([
                'company_id' => $companyId,
                'ticket_number' => $ticketNo,
                'customer_id' => $customerId,
                'brand' => $request->brand,
                'model' => $request->model,
                'imei_serial' => $request->imei_serial,
                'passcode_encrypted' => $passcodeEnc,
                'pattern_code_encrypted' => $patternEnc,
                'reported_faults' => $request->reported_faults,
                'physical_condition' => $request->physical_condition,
                'status' => 'received',
                'estimated_cost' => $estCost,
                'total_amount' => $estCost,
                'advance_paid' => $advPaid,
                'balance_due' => max(0.00, $estCost - $advPaid),
                'received_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('mobileshop.repairs')->with('success', "Repair ticket #{$ticketNo} successfully created!");
        });
    }

    /**
     * Update Repair Ticket Status & Consume Parts
     */
    public function updateRepairStatus(Request $request, $id)
    {
        abort_unless(auth()->check() && (auth()->user()->can('update-mobileshop-repairs') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('repair-technician')), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $id, $companyId) {
            $ticket = DB::table('ms_repair_tickets')->where('company_id', $companyId)->where('id', $id)->lockForUpdate()->first();
            if (!$ticket) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);
                }
                return redirect()->back()->with('error', 'Ticket not found');
            }

            $status = $request->status ?? $ticket->status;
            $laborCharge = $request->has('labor_charge') && $request->labor_charge !== null ? (float) $request->labor_charge : (float) $ticket->labor_charge;
            $totalPartsCost = (float) $ticket->parts_cost;
            $partQty = max(1, (int) ($request->part_qty ?? 1));

            // Consume part from inventory if selected
            if ($request->has('consumed_part_id') && !empty($request->consumed_part_id)) {
                $part = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('id', $request->consumed_part_id)->lockForUpdate()->first();
                if ($part) {
                    if ($part->stock_qty < $partQty) {
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => "Insufficient stock for {$part->name}! Available: {$part->stock_qty}"], 422);
                        }
                        return redirect()->back()->with('error', "Insufficient stock for {$part->name}! Available: {$part->stock_qty}");
                    }
                    
                    $newStock = $part->stock_qty - $partQty;
                    DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                        'stock_qty' => $newStock,
                        'updated_at' => now(),
                    ]);
                    
                    // Log deduction in history
                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id' => $part->id,
                        'type' => 'deduction',
                        'quantity' => $partQty,
                        'balance_after' => $newStock,
                        'reference' => 'Used in Repair Ticket #' . $ticket->ticket_number,
                        'user_id' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Record part usage against ticket
                    DB::table('ms_repair_ticket_parts')->insert([
                        'repair_ticket_id' => $ticket->id,
                        'part_id' => $part->id,
                        'quantity' => $partQty,
                        'unit_cost' => $part->unit_cost,
                        'unit_price' => $part->selling_price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalPartsCost += ((float) $part->selling_price * $partQty);
                }
            }

            $additionalPayment = (float) ($request->additional_payment ?? 0.00);
            $totalAdvance = (float) $ticket->advance_paid + $additionalPayment;
            $grandTotal = $laborCharge + $totalPartsCost;
            if ($grandTotal == 0 && (float) $ticket->estimated_cost > 0) {
                $grandTotal = (float) $ticket->estimated_cost;
            }
            $balanceDue = max(0.00, $grandTotal - $totalAdvance);

            DB::table('ms_repair_tickets')->where('id', $id)->update([
                'status' => $status,
                'labor_charge' => $laborCharge,
                'parts_cost' => $totalPartsCost,
                'total_amount' => $grandTotal,
                'advance_paid' => $totalAdvance,
                'balance_due' => $balanceDue,
                'completed_at' => ($status === 'ready' || $status === 'delivered') ? ($ticket->completed_at ?? now()) : $ticket->completed_at,
                'delivered_at' => ($status === 'delivered') ? ($ticket->delivered_at ?? now()) : $ticket->delivered_at,
                'updated_at' => now(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Ticket #{$ticket->ticket_number} updated to status: {$status}!",
                    'ticket' => [
                        'id' => $id,
                        'status' => $status,
                        'labor_charge' => $laborCharge,
                        'parts_cost' => $totalPartsCost,
                        'total_amount' => $grandTotal,
                        'advance_paid' => $totalAdvance,
                        'balance_due' => $balanceDue,
                    ]
                ]);
            }

            return redirect()->route('mobileshop.repairs')->with('success', "Ticket #{$ticket->ticket_number} updated to status: {$status}!");
        });
    }
}
