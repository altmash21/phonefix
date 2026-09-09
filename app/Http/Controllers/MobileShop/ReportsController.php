<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Reports\GstExportService;
use App\Services\MobileShop\Reports\ReportsQueryService;
use App\Services\MobileShop\Reports\SalesExportService;
use Illuminate\Http\Request;

class ReportsController extends BaseMobileShopController
{
    protected ReportsQueryService $queryService;
    protected SalesExportService $salesExportService;
    protected GstExportService $gstExportService;

    public function __construct(
        ?ReportsQueryService $queryService = null,
        ?SalesExportService $salesExportService = null,
        ?GstExportService $gstExportService = null
    ) {
        $this->queryService       = $queryService ?? new ReportsQueryService();
        $this->salesExportService = $salesExportService ?? new SalesExportService();
        $this->gstExportService   = $gstExportService ?? new GstExportService();
    }

    /**
     * Shared helper to retrieve filtered sales and purchases for reporting and CSV exports
     */
    protected function getSalesAndPurchasesForReports(Request $request): array
    {
        return $this->queryService->getSalesAndPurchasesForReports(
            $this->getCompanyId(),
            $this->getUserNiche(),
            $this->coverCategories,
            $request
        );
    }

    /**
     * Reports Center (Sales, Purchases, Stock Valuation, GST & Debtor Ledgers)
     */
    public function reports(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-reports-khata') ||
            auth()->user()->can('read-reports-financial') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized access to reports.');

        $data = $this->getSalesAndPurchasesForReports($request);
        $metrics = $this->queryService->calculateReportMetrics(
            $this->getCompanyId(),
            $this->getUserNiche(),
            $this->coverCategories,
            $data
        );

        return view('mobileshop.reports', $metrics);
    }

    /**
     * Export Filtered Sales Register to CSV / Excel
     */
    public function exportSalesCsv(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-reports-financial') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized access to export reports.');

        $data = $this->getSalesAndPurchasesForReports($request);
        return $this->salesExportService->exportSalesCsv($data);
    }

    /**
     * Export GSTR-1 & GSTR-3B Compliant Tax Statement to CSV / Excel
     */
    public function exportGstCsv(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-reports') ||
            auth()->user()->can('read-reports-financial') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized access to export GST report.');

        $data = $this->getSalesAndPurchasesForReports($request);
        return $this->gstExportService->exportGstCsv($data);
    }
}
