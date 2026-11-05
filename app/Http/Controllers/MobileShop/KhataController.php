<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class KhataController extends BaseMobileShopController
{
    /**
     * Customer Khata (Udhari) Screen — Scoped to Respective Logins / Roles
     */
    public function khata()
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('read-mobileshop-accessories') ||
            auth()->user()->can('read-mobileshop-repairs') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized access to customer khata.');

        $companyId = $this->getCompanyId();
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('store-admin');
        $isAccStaff = $user->hasRole('accessories-staff');
        $isTech = $user->hasRole('repair-technician');

        $txQuery = DB::table('ms_customer_khata_transactions')
            ->join('ms_customers', 'ms_customer_khata_transactions.customer_id', '=', 'ms_customers.id')
            ->select(
                'ms_customer_khata_transactions.*',
                'ms_customers.name as customer_name',
                'ms_customers.phone as customer_phone',
                'ms_customers.udhari_balance as current_customer_due'
            )
            ->where('ms_customer_khata_transactions.company_id', $companyId);

        // Scope transactions to the respective department/login
        if ($isAdmin) {
            // Admin sees all store transactions
        } elseif ($isAccStaff) {
            $txQuery->where(function ($q) use ($user) {
                $q->where('ms_customer_khata_transactions.recorded_by', $user->id)
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Accessory%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%ACC-%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Cover%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Tempered%');
            });
        } elseif ($isTech) {
            $txQuery->where(function ($q) use ($user) {
                $q->where('ms_customer_khata_transactions.recorded_by', $user->id)
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Repair%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Display%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Service%');
            });
        } else {
            $txQuery->where('ms_customer_khata_transactions.recorded_by', $user->id);
        }

        $transactions = $txQuery->orderBy('ms_customer_khata_transactions.id', 'desc')->get();

        foreach ($transactions as $tx) {
            if ($tx->type === 'adjustment' && !empty($tx->remarks) && str_contains($tx->remarks, 'Returned') && preg_match('/#([A-Za-z0-9\-_]+)/', $tx->remarks, $m)) {
                $inv = $m[1];
                $returnedItem = \App\Services\MobileShop\Common\MobileShopInvoiceResolver::resolveReturnedItemSummary($companyId, $inv, (float) $tx->amount);
                if ($returnedItem && !str_contains($tx->remarks, $returnedItem)) {
                    $tx->remarks = "Reversal for Returned: {$returnedItem} (Sale #{$inv})";
                }
            }
        }

        // Always fetch all customers for the store so operators can view and search any customer's udhari
        $customers = DB::table('ms_customers')
            ->where('company_id', $companyId)
            ->orderBy('udhari_balance', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        // Enrich customers with their last transaction date and activity count
        $lastTxs = DB::table('ms_customer_khata_transactions')
            ->where('company_id', $companyId)
            ->select('customer_id', DB::raw('MAX(created_at) as last_tx_date'), DB::raw('COUNT(id) as total_tx_count'))
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        foreach ($customers as $c) {
            $c->last_tx_date = isset($lastTxs[$c->id]) ? $lastTxs[$c->id]->last_tx_date : null;
            $c->total_tx_count = isset($lastTxs[$c->id]) ? (int)$lastTxs[$c->id]->total_tx_count : 0;
        }

        $accessoryInvoices = DB::table('ms_accessory_sales')
            ->where('company_id', $companyId)
            ->pluck('id', 'invoice_number')
            ->toArray();

        $mobileInvoices = DB::table('ms_mobile_sales')
            ->where('company_id', $companyId)
            ->pluck('id', 'invoice_number')
            ->toArray();

        return view('mobileshop.khata', compact('customers', 'transactions', 'accessoryInvoices', 'mobileInvoices', 'isAdmin'));
    }

    /**
     * Collect Khata Payment
     */
    public function collectKhata(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('create-sale-accessories') ||
            auth()->user()->can('create-mobileshop-pos') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized action.');

        $request->validate([
            'customer_id'   => 'required|exists:ms_customers,id',
            'amount'        => 'required|numeric|min:0.01',
            'payment_mode'  => 'required|in:cash,upi,cash+upi,bank_transfer,cheque',
            'cash_amount'   => 'nullable|numeric|min:0',
            'upi_amount'    => 'nullable|numeric|min:0',
            'reference_no'  => 'nullable|string|max:191',
            'remarks'       => 'nullable|string|max:500',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('id', $request->customer_id)->lockForUpdate()->first();
            if (!$customer) {
                return redirect()->back()->with('error', 'Customer not found.');
            }
            $amount = (float) $request->amount;
            $paymentMode = $request->payment_mode;
            $newBalance = max(0.00, $customer->udhari_balance - $amount);

            DB::table('ms_customers')->where('id', $customer->id)->update([
                'udhari_balance' => $newBalance,
                'updated_at'     => now(),
            ]);

            if ($paymentMode === 'cash+upi') {
                $cashPart = (float) ($request->cash_amount ?? 0);
                $upiPart  = (float) ($request->upi_amount ?? 0);

                if ($cashPart <= 0 && $upiPart <= 0) {
                    $cashPart = round($amount / 2, 2);
                    $upiPart  = round($amount - $cashPart, 2);
                } elseif ($cashPart > 0 && $upiPart <= 0) {
                    $upiPart = max(0.0, round($amount - $cashPart, 2));
                } elseif ($upiPart > 0 && $cashPart <= 0) {
                    $cashPart = max(0.0, round($amount - $upiPart, 2));
                }

                $userNotes = trim((string) ($request->remarks ?? ''));
                $userRef   = trim((string) ($request->reference_no ?? ''));

                $balAfterCash = max(0.00, $customer->udhari_balance - $cashPart);

                if ($cashPart > 0) {
                    DB::table('ms_customer_khata_transactions')->insert([
                        'company_id'    => $companyId,
                        'customer_id'   => $customer->id,
                        'type'          => 'payment_received',
                        'amount'        => $cashPart,
                        'balance_after' => $balAfterCash,
                        'payment_mode'  => 'cash',
                        'reference_no'  => $userRef ? "{$userRef} (Cash)" : null,
                        'remarks'       => $userNotes ? "{$userNotes} [Cash portion]" : 'Khata repayment received (Cash)',
                        'recorded_by'   => auth()->id(),
                        'created_at'    => now(),
                    ]);
                }

                if ($upiPart > 0) {
                    DB::table('ms_customer_khata_transactions')->insert([
                        'company_id'    => $companyId,
                        'customer_id'   => $customer->id,
                        'type'          => 'payment_received',
                        'amount'        => $upiPart,
                        'balance_after' => $newBalance,
                        'payment_mode'  => 'upi',
                        'reference_no'  => $userRef ? "{$userRef} (UPI)" : null,
                        'remarks'       => $userNotes ? "{$userNotes} [UPI portion]" : 'Khata repayment received (UPI)',
                        'recorded_by'   => auth()->id(),
                        'created_at'    => now()->addSecond(),
                    ]);
                }

                return redirect()->route('mobileshop.khata')->with('success', "Split repayment of ₹" . number_format($amount, 2) . " (Cash: ₹" . number_format($cashPart, 2) . ", UPI: ₹" . number_format($upiPart, 2) . ") received from {$customer->name}! New balance: ₹" . number_format($newBalance, 2));
            }

            DB::table('ms_customer_khata_transactions')->insert([
                'company_id'    => $companyId,
                'customer_id'   => $customer->id,
                'type'          => 'payment_received',
                'amount'        => $amount,
                'balance_after' => $newBalance,
                'payment_mode'  => $paymentMode,
                'reference_no'  => $request->reference_no,
                'remarks'       => $request->remarks ?? 'Khata repayment received',
                'recorded_by'   => auth()->id(),
                'created_at'    => now(),
            ]);

            return redirect()->route('mobileshop.khata')->with('success', "Repayment of ₹" . number_format($amount, 2) . " received via " . strtoupper($paymentMode) . " from {$customer->name}! New balance: ₹" . number_format($newBalance, 2));
        });
    }

    /**
     * Add Old Udhar / Khata Directly to Database
     */
    public function addOldUdhar(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('create-sale-accessories') ||
            auth()->user()->can('create-mobileshop-pos') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized action.');

        $request->validate([
            'customer_id'    => 'nullable|exists:ms_customers,id',
            'customer_name'  => 'required_without:customer_id|nullable|string|max:191',
            'customer_phone' => 'required_without:customer_id|nullable|string|max:50',
            'amount'         => 'required|numeric|min:0.01',
            'date'           => 'nullable|date',
            'reference_no'   => 'nullable|string|max:191',
            'description'    => 'nullable|string|max:500',
            'remarks'        => 'nullable|string|max:500',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $customer = null;

            if ($request->filled('customer_id')) {
                $customer = DB::table('ms_customers')
                    ->where('company_id', $companyId)
                    ->where('id', $request->customer_id)
                    ->lockForUpdate()
                    ->first();
            }

            if (!$customer && $request->filled('customer_phone')) {
                $cleanPhone = trim($request->customer_phone);
                $customer = DB::table('ms_customers')
                    ->where('company_id', $companyId)
                    ->where('phone', $cleanPhone)
                    ->lockForUpdate()
                    ->first();

                if (!$customer) {
                    $newCustId = DB::table('ms_customers')->insertGetId([
                        'company_id'     => $companyId,
                        'name'           => trim($request->customer_name ?: 'Customer ' . $cleanPhone),
                        'phone'          => $cleanPhone,
                        'udhari_balance' => 0.00,
                        'credit_limit'   => 10000.00,
                        'state_code'     => '09',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);

                    $customer = DB::table('ms_customers')->where('id', $newCustId)->first();
                }
            }

            if (!$customer) {
                return redirect()->back()->with('error', 'Please select or enter customer details.');
            }

            $amount = (float) $request->amount;
            $newBalance = (float) $customer->udhari_balance + $amount;

            DB::table('ms_customers')->where('id', $customer->id)->update([
                'udhari_balance' => $newBalance,
                'updated_at'     => now(),
            ]);

            $txDate = $request->filled('date') ? \Carbon\Carbon::parse($request->date) : now();

            $desc = trim((string) ($request->input('description') ?? $request->input('remarks') ?? 'Old Udhar / Opening Balance'));
            if (empty($desc)) {
                $desc = 'Old Udhar / Opening Balance';
            }

            DB::table('ms_customer_khata_transactions')->insert([
                'company_id'    => $companyId,
                'customer_id'   => $customer->id,
                'type'          => 'opening_balance',
                'amount'        => $amount,
                'balance_after' => $newBalance,
                'payment_mode'  => null,
                'reference_no'  => $request->reference_no ?: 'OLD-UDHAR',
                'remarks'       => $desc,
                'recorded_by'   => auth()->id(),
                'created_at'    => $txDate,
            ]);

            // Clear customers cache
            \Illuminate\Support\Facades\Cache::forget("ms_customers_picker_v2_{$companyId}");

            return redirect()->route('mobileshop.khata')
                ->with('success', "Old Udhar of ₹" . number_format($amount, 2) . " added directly to {$customer->name}'s Khata! Total Due: ₹" . number_format($newBalance, 2));
        });
    }

    /**
     * Customer Account Statement / Tally Ledger View (A4 Printable)
     */
    public function customerStatement(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('read-mobileshop-accessories') ||
            auth()->user()->can('read-mobileshop-dashboard') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized access to customer statement.');

        $companyId = $this->getCompanyId();
        $filters = [
            'filter' => $request->get('filter', 'active'),
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
        ];
        $statement = $this->buildCustomerLedgerStatement($companyId, (int) $id, $filters);

        if (!$statement['customer']) {
            abort(404, 'Customer not found.');
        }

        return view('mobileshop.customer_statement', $statement);
    }

    /**
     * Download Customer Statement PDF
     */
    public function customerStatementPdf(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized access to customer statement PDF.');

        $companyId = $this->getCompanyId();
        $filters = [
            'filter' => $request->get('filter', 'active'),
            'from'   => $request->get('from'),
            'to'     => $request->get('to'),
        ];
        $statement = $this->buildCustomerLedgerStatement($companyId, (int) $id, $filters);

        if (!$statement['customer']) {
            abort(404, 'Customer not found.');
        }

        $pdf = Pdf::loadView('mobileshop.pdf.customer_statement', $statement);
        $pdf->setPaper('a4', 'portrait');
        $safeName = Str::slug($statement['customer']->name, '_');
        $suffix = $statement['currentFilter'] === 'active' ? 'ActiveDues' : Str::slug($statement['periodLabel'], '_');
        return $pdf->download("Statement-{$safeName}-{$suffix}.pdf");
    }

    /**
     * Delete Duplicate Khata Entry (Statement Edit / Correction)
     */
    public function deleteTransaction(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-khata') ||
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($id, $companyId) {
            $tx = DB::table('ms_customer_khata_transactions')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$tx) {
                return redirect()->back()->with('error', 'Khata entry not found or already deleted.');
            }

            $customerId = (int) $tx->customer_id;
            $amount = (float) $tx->amount;
            $isDebit = in_array($tx->type, ['opening_balance', 'old_udhar', 'debit_adjustment', 'udhari_sale']);

            DB::table('ms_customer_khata_transactions')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->delete();

            // Recalculate customer's balance accurately
            $newBalance = self::syncCustomerUdhariBalance($companyId, $customerId);

            $typeLabel = $isDebit ? 'Debit / Old Udhar' : 'Payment / Credit';
            return redirect()->back()->with('success', "Duplicate {$typeLabel} entry of ₹" . number_format($amount, 2) . " deleted successfully! Customer balance is now ₹" . number_format($newBalance, 2) . ".");
        });
    }

    /**
     * Update Khata Entry (Statement Edit / Correction)
     */
    public function updateTransaction(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-khata') ||
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized action.');

        $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'date'         => 'nullable|date',
            'remarks'      => 'nullable|string|max:500',
            'reference_no' => 'nullable|string|max:191',
            'payment_mode' => 'nullable|string|in:cash,upi,bank_transfer,cheque,cash+upi',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $id, $companyId) {
            $tx = DB::table('ms_customer_khata_transactions')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$tx) {
                return redirect()->back()->with('error', 'Khata entry not found.');
            }

            $customerId = (int) $tx->customer_id;
            $newAmount = (float) $request->amount;
            $txDate = $request->filled('date') ? \Carbon\Carbon::parse($request->date) : $tx->created_at;

            $updateData = [
                'amount'       => $newAmount,
                'created_at'   => $txDate,
                'reference_no' => $request->filled('reference_no') ? $request->reference_no : $tx->reference_no,
                'remarks'      => $request->filled('remarks') ? $request->remarks : $tx->remarks,
            ];

            if ($request->filled('payment_mode')) {
                $updateData['payment_mode'] = $request->payment_mode;
            }

            DB::table('ms_customer_khata_transactions')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->update($updateData);

            // Recalculate customer's balance accurately
            $newBalance = self::syncCustomerUdhariBalance($companyId, $customerId);

            return redirect()->back()->with('success', "Khata entry updated successfully! Customer balance is now ₹" . number_format($newBalance, 2) . ".");
        });
    }

    /**
     * Recalculate and Sync Customer Udhari Balance against Ledger
     */
    public function syncCustomerBalance(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('create-mobileshop-khata') ||
            auth()->user()->can('read-mobileshop-khata') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();
        $balance = self::syncCustomerUdhariBalance($companyId, (int) $id);

        return redirect()->back()->with('success', "Customer balance verified and synchronized with ledger! Current Due: ₹" . number_format($balance, 2));
    }

    /**
     * Helper to compute true balance from full ledger and update ms_customers table
     */
    public static function syncCustomerUdhariBalance(int $companyId, int $customerId): float
    {
        $statement = \App\Services\MobileShop\Common\MobileShopInvoiceResolver::buildCustomerLedgerStatement($companyId, $customerId, ['filter' => 'all']);
        $closingBalance = (float) ($statement['closingBalance'] ?? 0.00);

        DB::table('ms_customers')
            ->where('company_id', $companyId)
            ->where('id', $customerId)
            ->update([
                'udhari_balance' => $closingBalance,
                'updated_at'     => now(),
            ]);

        \Illuminate\Support\Facades\Cache::forget("ms_customers_picker_v2_{$companyId}");

        return $closingBalance;
    }
}
