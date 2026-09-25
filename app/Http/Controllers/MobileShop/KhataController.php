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
            'customer_id' => 'required|exists:ms_customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('id', $request->customer_id)->lockForUpdate()->first();
            if (!$customer) {
                return redirect()->back()->with('error', 'Customer not found.');
            }
            $amount = (float) $request->amount;
            $newBalance = max(0.00, $customer->udhari_balance - $amount);

            DB::table('ms_customers')->where('id', $customer->id)->update([
                'udhari_balance' => $newBalance,
                'updated_at' => now(),
            ]);

            DB::table('ms_customer_khata_transactions')->insert([
                'company_id' => $companyId,
                'customer_id' => $customer->id,
                'type' => 'payment_received',
                'amount' => $amount,
                'balance_after' => $newBalance,
                'payment_mode' => $request->payment_mode,
                'reference_no' => $request->reference_no,
                'remarks' => $request->remarks ?? 'Khata repayment received',
                'recorded_by' => auth()->id(),
                'created_at' => now(),
            ]);

            return redirect()->route('mobileshop.khata')->with('success', "Repayment of ₹" . number_format($amount, 2) . " received from {$customer->name}! New balance: ₹" . number_format($newBalance, 2));
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
            'remarks'        => 'nullable|string|max:191',
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

            DB::table('ms_customer_khata_transactions')->insert([
                'company_id'    => $companyId,
                'customer_id'   => $customer->id,
                'type'          => 'opening_balance',
                'amount'        => $amount,
                'balance_after' => $newBalance,
                'payment_mode'  => null,
                'reference_no'  => $request->reference_no ?: 'OLD-KHATA',
                'remarks'       => $request->remarks ?: 'Old Udhar / Opening Balance',
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
    public function customerStatement($id)
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
        $statement = $this->buildCustomerLedgerStatement($companyId, (int) $id);

        if (!$statement['customer']) {
            abort(404, 'Customer not found.');
        }

        return view('mobileshop.customer_statement', $statement);
    }

    /**
     * Download Customer Statement PDF
     */
    public function customerStatementPdf($id)
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
        $statement = $this->buildCustomerLedgerStatement($companyId, (int) $id);

        if (!$statement['customer']) {
            abort(404, 'Customer not found.');
        }

        $pdf = Pdf::loadView('mobileshop.pdf.customer_statement', $statement);
        $pdf->setPaper('a4', 'portrait');
        $safeName = Str::slug($statement['customer']->name, '_');
        return $pdf->download("Statement-{$safeName}.pdf");
    }
}
