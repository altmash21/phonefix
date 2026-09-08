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
            auth()->user()->hasRole('sales-staff') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('cover-staff') ||
            auth()->user()->hasRole('repair-technician')
        ), 403, 'Unauthorized access to customer khata.');

        $companyId = $this->getCompanyId();
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('store-admin');
        $isSalesStaff = $user->hasRole('sales-staff');
        $isAccStaff = $user->hasRole('accessories-staff') || $user->hasRole('cover-staff');
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
        } elseif ($isSalesStaff) {
            $txQuery->where(function ($q) use ($user) {
                $q->where('ms_customer_khata_transactions.recorded_by', $user->id)
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Mobile%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%MOB-%')
                  ->orWhere('ms_customer_khata_transactions.remarks', 'LIKE', '%Phone%');
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

        // Scope customers list
        if ($isAdmin) {
            $customers = DB::table('ms_customers')
                ->where('company_id', $companyId)
                ->orderBy('udhari_balance', 'desc')
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $relevantCustomerIds = $transactions->pluck('customer_id')->unique()->toArray();
            $customers = DB::table('ms_customers')
                ->where('company_id', $companyId)
                ->whereIn('id', $relevantCustomerIds)
                ->orderBy('udhari_balance', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            // If empty, fetch all to allow selecting any customer for repayment
            if ($customers->isEmpty()) {
                $customers = DB::table('ms_customers')
                    ->where('company_id', $companyId)
                    ->orderBy('udhari_balance', 'desc')
                    ->orderBy('name', 'asc')
                    ->get();
            }
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
            auth()->user()->hasRole('sales-staff') ||
            auth()->user()->hasRole('accessories-staff') ||
            auth()->user()->hasRole('cover-staff') ||
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
            auth()->user()->hasRole('sales-staff')
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
            auth()->user()->hasRole('sales-staff')
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
