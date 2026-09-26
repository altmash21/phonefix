<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends BaseMobileShopController
{
    /**
     * Categories list with human-friendly labels and icons
     */
    public static array $categories = [
        'shop_rent'          => ['label' => 'Shop Rent',             'icon' => 'home'],
        'electricity_bills'  => ['label' => 'Electricity & Power',   'icon' => 'zap'],
        'salary_wages'       => ['label' => 'Staff Salary & Wages',  'icon' => 'users'],
        'tea_refreshment'    => ['label' => 'Tea & Refreshments',    'icon' => 'coffee'],
        'transport_freight'  => ['label' => 'Courier & Transport',   'icon' => 'truck'],
        'tools_equipment'    => ['label' => 'Tools & Equipment',     'icon' => 'wrench'],
        'shop_maintenance'   => ['label' => 'Shop Maintenance',      'icon' => 'tool'],
        'internet_phone'     => ['label' => 'Internet & Mobile Bill','icon' => 'wifi'],
        'marketing_ads'      => ['label' => 'Marketing & Printing',  'icon' => 'megaphone'],
        'packaging_materials'=> ['label' => 'Packaging Materials',   'icon' => 'package'],
        'taxes_government'   => ['label' => 'Taxes & Licenses',      'icon' => 'file-text'],
        'other'              => ['label' => 'General / Other',       'icon' => 'more-horizontal'],
    ];

    /**
     * Shop Expenses List & Expense Dashboard
     */
    public function index(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-dashboard') ||
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-admin-panel') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized access to shop expenses.');

        $companyId = $this->getCompanyId();

        // Date filter handling
        $filterPeriod = $request->get('period', 'this_month');
        $fromDate = $request->get('from');
        $toDate = $request->get('to');
        $selectedCat = $request->get('category');
        $selectedMode = $request->get('payment_mode');
        $searchQuery = trim($request->get('q', ''));

        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        if ($filterPeriod === 'today') {
            $fromDate = $today;
            $toDate = $today;
        } elseif ($filterPeriod === 'this_month') {
            $fromDate = $startOfMonth;
            $toDate = $endOfMonth;
        } elseif ($filterPeriod === 'last_month') {
            $fromDate = $startOfLastMonth;
            $toDate = $endOfLastMonth;
        }

        // Base query
        $query = DB::table('ms_expenses')
            ->leftJoin('users', 'ms_expenses.created_by', '=', 'users.id')
            ->select('ms_expenses.*', 'users.name as recorded_by_name')
            ->where('ms_expenses.company_id', $companyId);

        if ($fromDate && $toDate) {
            $query->whereBetween('ms_expenses.expense_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('ms_expenses.expense_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('ms_expenses.expense_date', '<=', $toDate);
        }

        if ($selectedCat) {
            $query->where('ms_expenses.category', $selectedCat);
        }

        if ($selectedMode) {
            $query->where('ms_expenses.payment_mode', $selectedMode);
        }

        if ($searchQuery !== '') {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('ms_expenses.title', 'like', "%{$searchQuery}%")
                  ->orWhere('ms_expenses.paid_to', 'like', "%{$searchQuery}%")
                  ->orWhere('ms_expenses.expense_number', 'like', "%{$searchQuery}%")
                  ->orWhere('ms_expenses.reference_no', 'like', "%{$searchQuery}%");
            });
        }

        $expenses = $query->orderBy('ms_expenses.expense_date', 'desc')
                          ->orderBy('ms_expenses.id', 'desc')
                          ->paginate(50)
                          ->appends($request->all());

        // Overview KPI metrics
        $todayExpenses = (float) DB::table('ms_expenses')
            ->where('company_id', $companyId)
            ->whereDate('expense_date', $today)
            ->sum('amount');

        $monthExpenses = (float) DB::table('ms_expenses')
            ->where('company_id', $companyId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $filteredTotal = (float) (clone $query)->sum('ms_expenses.amount');

        $cashExpenses = (float) DB::table('ms_expenses')
            ->where('company_id', $companyId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->where('payment_mode', 'cash')
            ->sum('amount');

        $upiExpenses = (float) DB::table('ms_expenses')
            ->where('company_id', $companyId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->whereIn('payment_mode', ['upi', 'bank_transfer'])
            ->sum('amount');

        // Category breakdown for current month
        $categoryBreakdown = DB::table('ms_expenses')
            ->select('category', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->where('company_id', $companyId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        return view('mobileshop.expenses', [
            'expenses'          => $expenses,
            'categories'        => self::$categories,
            'todayExpenses'     => $todayExpenses,
            'monthExpenses'     => $monthExpenses,
            'filteredTotal'     => $filteredTotal,
            'cashExpenses'      => $cashExpenses,
            'upiExpenses'       => $upiExpenses,
            'categoryBreakdown' => $categoryBreakdown,
            'filterPeriod'      => $filterPeriod,
            'fromDate'          => $fromDate,
            'toDate'            => $toDate,
            'selectedCat'       => $selectedCat,
            'selectedMode'      => $selectedMode,
            'searchQuery'       => $searchQuery,
        ]);
    }

    /**
     * Record a new shop expense
     */
    public function store(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-dashboard') ||
            auth()->user()->can('read-admin-panel') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->hasRole('accessories-staff')
        ), 403, 'Unauthorized to record expense.');

        $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'category'     => 'required|string|max:60',
            'expense_date' => 'required|date',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque,other',
            'paid_to'      => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:100',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $companyId = $this->getCompanyId();

        // Generate unique atomic sequence number: EXP-YYMM-####
        $prefix = 'EXP-' . Carbon::parse($request->expense_date)->format('ym') . '-';
        $lastExpense = DB::table('ms_expenses')
            ->where('company_id', $companyId)
            ->where('expense_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($lastExpense && preg_match('/-(\d+)$/', $lastExpense->expense_number, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }
        $expenseNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        DB::table('ms_expenses')->insert([
            'company_id'     => $companyId,
            'expense_number' => $expenseNumber,
            'category'       => $request->category,
            'title'          => trim($request->title),
            'amount'         => (float) $request->amount,
            'expense_date'   => $request->expense_date,
            'payment_mode'   => $request->payment_mode,
            'paid_to'        => $request->paid_to ? trim($request->paid_to) : null,
            'reference_no'   => $request->reference_no ? trim($request->reference_no) : null,
            'notes'          => $request->notes ? trim($request->notes) : null,
            'created_by'     => auth()->id(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->back()->with('success', "Expense recorded successfully: {$expenseNumber} (₹" . number_format($request->amount, 2) . ")");
    }

    /**
     * Delete an expense entry (Store Admin or Admin only)
     */
    public function destroy($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin') ||
            auth()->user()->can('read-admin-panel')
        ), 403, 'Only administrators can delete expenses.');

        $companyId = $this->getCompanyId();

        $deleted = DB::table('ms_expenses')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Expense record deleted successfully.');
        }

        return redirect()->back()->with('error', 'Expense record not found.');
    }

    /**
     * Export expenses to CSV
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-dashboard') ||
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized to export expenses.');

        $companyId = $this->getCompanyId();

        $query = DB::table('ms_expenses')
            ->leftJoin('users', 'ms_expenses.created_by', '=', 'users.id')
            ->select('ms_expenses.*', 'users.name as recorded_by_name')
            ->where('ms_expenses.company_id', $companyId)
            ->orderBy('ms_expenses.expense_date', 'desc');

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('ms_expenses.expense_date', [$request->from, $request->to]);
        }
        if ($request->filled('category')) {
            $query->where('ms_expenses.category', $request->category);
        }
        if ($request->filled('payment_mode')) {
            $query->where('ms_expenses.payment_mode', $request->payment_mode);
        }

        $records = $query->get();
        $filename = 'shop_expenses_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Expense #', 'Date', 'Category', 'Title', 'Amount (INR)', 'Payment Mode', 'Paid To', 'Reference / UTR', 'Notes', 'Recorded By']);

            foreach ($records as $r) {
                $catLabel = self::$categories[$r->category]['label'] ?? ucfirst(str_replace('_', ' ', $r->category));
                fputcsv($handle, [
                    $r->expense_number,
                    $r->expense_date,
                    $catLabel,
                    $r->title,
                    number_format($r->amount, 2, '.', ''),
                    strtoupper($r->payment_mode),
                    $r->paid_to ?? '',
                    $r->reference_no ?? '',
                    $r->notes ?? '',
                    $r->recorded_by_name ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
