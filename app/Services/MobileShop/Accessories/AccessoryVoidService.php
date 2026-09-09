<?php

namespace App\Services\MobileShop\Accessories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessoryVoidService
{
    /**
     * Process sales return / void for accessory sale
     */
    public function voidAccessorySale(int $companyId, int $id, Request $request, bool $isOwner, callable $verifyOtpCallback): array
    {
        $request->validate([
            'void_reason'        => 'nullable|string',
            'return_reason_code' => 'nullable|string',
        ]);

        $shouldRestock       = $request->has('should_restock') ? (bool) $request->should_restock : true;
        $reasonLabel         = $request->input('reason_label') ?: ($request->input('return_reason_code') ?: 'Customer Return');
        $customDetails       = $request->input('void_reason') ? " — {$request->input('void_reason')}" : "";
        $auditReason         = "{$reasonLabel}{$customDetails}";
        $returnedItemsInput  = $request->input('returned_items', []);

        // OTP Security Gate: Only owner can void without OTP
        $itemRef = "acc_sale:{$id}";
        if (!$isOwner) {
            $otpCode = $request->input('otp_code');
            if (!$verifyOtpCallback($companyId, 'void_accessory_sale', $itemRef, $otpCode)) {
                return [
                    'success'        => false,
                    'otp_required'   => true,
                    'action'         => 'void_accessory_sale',
                    'item_reference' => $itemRef,
                    'message'        => 'Store Owner OTP authorization is required to void sales invoices.',
                ];
            }
        }

        return DB::transaction(function () use ($id, $companyId, $shouldRestock, $auditReason, $returnedItemsInput) {
            $sale = DB::table('ms_accessory_sales')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$sale || $sale->status === 'voided') {
                return [
                    'success' => false,
                    'message' => 'Sale is already voided or cannot be found.',
                ];
            }

            $items = DB::table('ms_accessory_sale_items')
                ->where('company_id', $companyId)
                ->where('accessory_sale_id', $id)
                ->get();

            $totalRefundCalculated   = 0.00;
            $itemsProcessedCount     = 0;
            $totalOriginalItemsCount = $items->count();

            foreach ($items as $item) {
                // Determine if this item is selected for return
                $returnQty = 0;
                if (!empty($returnedItemsInput)) {
                    if (isset($returnedItemsInput[$item->id]) && (int) $returnedItemsInput[$item->id] > 0) {
                        $returnQty = min((int) $returnedItemsInput[$item->id], $item->quantity);
                    }
                } else {
                    // Full return fallback
                    $returnQty = $item->quantity;
                }

                if ($returnQty <= 0) {
                    continue;
                }

                $itemsProcessedCount++;
                $unitPrice = $item->unit_price > 0 ? (float) $item->unit_price : ((float) $item->line_total / max(1, $item->quantity));
                $itemRefund = round($unitPrice * $returnQty, 2);
                $totalRefundCalculated += $itemRefund;

                $part = DB::table('ms_parts_inventory')->where('id', $item->part_id)->lockForUpdate()->first();
                if ($part) {
                    if ($shouldRestock) {
                        $restoredStock = $part->stock_qty + $returnQty;
                        DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                            'stock_qty'  => $restoredStock,
                            'updated_at' => now(),
                        ]);

                        // Log stock restoration
                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id'       => $part->id,
                            'type'          => 'addition',
                            'quantity'      => $returnQty,
                            'balance_after' => $restoredStock,
                            'reference'     => "RESTOCKED: {$returnQty}x {$item->part_name} from Sale #{$sale->invoice_number} ({$auditReason}) by " . auth()->user()->name,
                            'user_id'       => auth()->id(),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    } else {
                        // Log as Defective / Quarantined without increasing sellable stock
                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id'       => $part->id,
                            'type'          => 'deduction',
                            'quantity'      => $returnQty,
                            'balance_after' => $part->stock_qty,
                            'reference'     => "DEFECTIVE RETURN (Quarantined/Scrap): {$returnQty}x {$item->part_name} from Sale #{$sale->invoice_number} ({$auditReason}) by " . auth()->user()->name,
                            'user_id'       => auth()->id(),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }

            if ($itemsProcessedCount === 0) {
                return [
                    'success' => false,
                    'message' => 'Please select at least one item to return.',
                ];
            }

            // Reverse Customer Khata Balance if Udhari was recorded
            if ($sale->udhari_amount > 0 && $sale->customer_id) {
                $customer = DB::table('ms_customers')->where('id', $sale->customer_id)->lockForUpdate()->first();
                if ($customer) {
                    $khataReversal = min($totalRefundCalculated, (float) $sale->udhari_amount);
                    $newBal = max(0.00, $customer->udhari_balance - $khataReversal);
                    DB::table('ms_customers')->where('id', $customer->id)->update([
                        'udhari_balance' => $newBal,
                        'updated_at'     => now(),
                    ]);

                    DB::table('ms_customer_khata_transactions')->insert([
                        'company_id'    => $companyId,
                        'customer_id'   => $customer->id,
                        'type'          => 'adjustment',
                        'amount'        => $khataReversal,
                        'balance_after' => $newBal,
                        'remarks'       => "Reversal for Returned Items on Sale #{$sale->invoice_number}",
                        'recorded_by'   => auth()->id(),
                        'created_at'    => now(),
                    ]);
                }
            }

            // Mark Header status (voided if all items returned, or partially_returned)
            $isAllReturned = ($itemsProcessedCount >= $totalOriginalItemsCount && $totalRefundCalculated >= (float) $sale->total_amount);
            $newStatus = $isAllReturned ? 'voided' : 'partially_returned';

            DB::table('ms_accessory_sales')->where('id', $id)->update([
                'status'      => $newStatus,
                'voided_by'   => auth()->id(),
                'voided_at'   => now(),
                'void_reason' => $auditReason . ($shouldRestock ? " [Restocked]" : " [Quarantined]"),
                'updated_at'  => now(),
            ]);

            $restockMsg = $shouldRestock ? 'and restocked to inventory' : 'and quarantined (defective)';
            return [
                'success' => true,
                'message' => "Return processed successfully! Refund Amount: ₹" . number_format($totalRefundCalculated, 2) . " ({$itemsProcessedCount} item(s) returned {$restockMsg}).",
            ];
        });
    }
}
