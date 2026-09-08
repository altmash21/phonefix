<?php

namespace App\Http\Controllers;

use App\Http\Controllers\MobileShop\BaseMobileShopController;
use App\Http\Controllers\MobileShop\DashboardController;
use App\Http\Controllers\MobileShop\SalesController;
use App\Http\Controllers\MobileShop\PurchaseController;
use App\Http\Controllers\MobileShop\StockController;
use App\Http\Controllers\MobileShop\AccessoriesController;
use App\Http\Controllers\MobileShop\RepairsController;
use App\Http\Controllers\MobileShop\KhataController;
use App\Http\Controllers\MobileShop\EmiController;
use App\Http\Controllers\MobileShop\ReportsController;
use App\Http\Controllers\MobileShop\MastersController;
use App\Http\Controllers\MobileShop\PublicStoreController;
use Illuminate\Http\Request;

/**
 * MobileShopController (Modular Facade & Backward-Compatibility Delegator)
 * 
 * The monolithic ~6,000 line controller has been decomposed into dedicated domain controllers:
 * - App\Http\Controllers\MobileShop\DashboardController
 * - App\Http\Controllers\MobileShop\SalesController
 * - App\Http\Controllers\MobileShop\PurchaseController
 * - App\Http\Controllers\MobileShop\StockController
 * - App\Http\Controllers\MobileShop\AccessoriesController
 * - App\Http\Controllers\MobileShop\RepairsController
 * - App\Http\Controllers\MobileShop\KhataController
 * - App\Http\Controllers\MobileShop\EmiController
 * - App\Http\Controllers\MobileShop\ReportsController
 * - App\Http\Controllers\MobileShop\MastersController
 * - App\Http\Controllers\MobileShop\PublicStoreController
 * 
 * This class extends BaseMobileShopController and delegates calls to the respective controllers,
 * ensuring 100% backward compatibility for any legacy callers, services, or test suites.
 */
class MobileShopController extends BaseMobileShopController
{
    /* ══════════════════════════════════════════════════════════════════════
       DASHBOARD
       ══════════════════════════════════════════════════════════════════════ */
    public function dashboard()
    {
        return app(DashboardController::class)->dashboard();
    }

    /* ══════════════════════════════════════════════════════════════════════
       SALES & POS
       ══════════════════════════════════════════════════════════════════════ */
    public function salesHub(Request $request)
    {
        return app(SalesController::class)->salesHub($request);
    }

    public function pos()
    {
        return app(SalesController::class)->pos();
    }

    public function scanEmiBill(Request $request)
    {
        return app(SalesController::class)->scanEmiBill($request);
    }

    public function processSale(Request $request)
    {
        return app(SalesController::class)->processSale($request);
    }

    public function invoice($id)
    {
        return app(SalesController::class)->invoice($id);
    }

    public function invoicePdf($id)
    {
        return app(SalesController::class)->invoicePdf($id);
    }

    public function voidMobileSale(Request $request, $id)
    {
        return app(SalesController::class)->voidMobileSale($request, $id);
    }

    public function storeSale(Request $request)
    {
        return app(SalesController::class)->storeSale($request);
    }

    public function saleCreate()
    {
        return app(SalesController::class)->saleCreate();
    }

    public function storeMultiSale(Request $request)
    {
        return app(SalesController::class)->storeMultiSale($request);
    }

    /* ══════════════════════════════════════════════════════════════════════
       PURCHASE & INWARD PROCUREMENT
       ══════════════════════════════════════════════════════════════════════ */
    public function purchaseHub(Request $request)
    {
        return app(PurchaseController::class)->purchaseHub($request);
    }

    public function purchaseCreate()
    {
        return app(PurchaseController::class)->purchaseCreate();
    }

    public function storePurchase(Request $request)
    {
        return app(PurchaseController::class)->storePurchase($request);
    }

    public function storeBulkPurchase(Request $request)
    {
        return app(PurchaseController::class)->storeBulkPurchase($request);
    }

    public function purchaseOrders()
    {
        return app(PurchaseController::class)->purchaseOrders();
    }

    public function recordSupplierPayment(Request $request)
    {
        return app(PurchaseController::class)->recordSupplierPayment($request);
    }

    public function updateSupplier(Request $request)
    {
        return app(PurchaseController::class)->updateSupplier($request);
    }

    public function purchaseInvoice(Request $request, $id)
    {
        return app(PurchaseController::class)->purchaseInvoice($request, $id);
    }

    public function purchaseInvoicePdf(Request $request, $id)
    {
        return app(PurchaseController::class)->purchaseInvoicePdf($request, $id);
    }

    /* ══════════════════════════════════════════════════════════════════════
       STOCK & INVENTORY
       ══════════════════════════════════════════════════════════════════════ */
    public function stockHub(Request $request)
    {
        return app(StockController::class)->stockHub($request);
    }

    public function storeStock(Request $request)
    {
        return app(StockController::class)->storeStock($request);
    }

    public function updateStock(Request $request, $id)
    {
        return app(StockController::class)->updateStock($request, $id);
    }

    public function deleteStockItem(Request $request)
    {
        return app(StockController::class)->deleteStockItem($request);
    }

    public function getStockHistory($type, $id)
    {
        return app(StockController::class)->getStockHistory($type, $id);
    }

    public function newMobiles()
    {
        return app(StockController::class)->newMobiles();
    }

    public function storeNewMobile(Request $request)
    {
        return app(StockController::class)->storeNewMobile($request);
    }

    public function secondHand()
    {
        return app(StockController::class)->secondHand();
    }

    public function storeSecondHand(Request $request)
    {
        return app(StockController::class)->storeSecondHand($request);
    }

    public function sellSecondHand(Request $request)
    {
        return app(StockController::class)->sellSecondHand($request);
    }

    /* ══════════════════════════════════════════════════════════════════════
       ACCESSORIES & SPARE PARTS
       ══════════════════════════════════════════════════════════════════════ */
    public function accessoriesPurchase(Request $request)
    {
        return app(AccessoriesController::class)->accessoriesPurchase($request);
    }

    public function storePart(Request $request)
    {
        return app(AccessoriesController::class)->storePart($request);
    }

    public function bulkRestock(Request $request)
    {
        return app(AccessoriesController::class)->bulkRestock($request);
    }

    public function sellAccessory(Request $request)
    {
        return app(AccessoriesController::class)->sellAccessory($request);
    }

    public function accessoryInvoice($id)
    {
        return app(AccessoriesController::class)->accessoryInvoice($id);
    }

    public function accessoryInvoicePdf($id)
    {
        return app(AccessoriesController::class)->accessoryInvoicePdf($id);
    }

    public function voidAccessorySale(Request $request, $id)
    {
        return app(AccessoriesController::class)->voidAccessorySale($request, $id);
    }

    public function getPartHistory($id)
    {
        return app(AccessoriesController::class)->getPartHistory($id);
    }

    public function getCategories()
    {
        return app(AccessoriesController::class)->getCategories();
    }

    public function storeCategory(Request $request)
    {
        return app(AccessoriesController::class)->storeCategory($request);
    }

    public function deleteCategory(Request $request, $id)
    {
        return app(AccessoriesController::class)->deleteCategory($request, $id);
    }

    public function searchParts(Request $request)
    {
        return app(AccessoriesController::class)->searchParts($request);
    }

    /* ══════════════════════════════════════════════════════════════════════
       REPAIRS SERVICE DESK
       ══════════════════════════════════════════════════════════════════════ */
    public function repairs(Request $request)
    {
        return app(RepairsController::class)->repairs($request);
    }

    public function storeRepair(Request $request)
    {
        return app(RepairsController::class)->storeRepair($request);
    }

    public function updateRepairStatus(Request $request, $id)
    {
        return app(RepairsController::class)->updateRepairStatus($request, $id);
    }

    /* ══════════════════════════════════════════════════════════════════════
       CUSTOMER KHATA (CREDIT LEDGER)
       ══════════════════════════════════════════════════════════════════════ */
    public function khata()
    {
        return app(KhataController::class)->khata();
    }

    public function collectKhata(Request $request)
    {
        return app(KhataController::class)->collectKhata($request);
    }

    public function customerStatement($id)
    {
        return app(KhataController::class)->customerStatement($id);
    }

    public function customerStatementPdf($id)
    {
        return app(KhataController::class)->customerStatementPdf($id);
    }

    /* ══════════════════════════════════════════════════════════════════════
       EMI & FINANCE PARTNERS
       ══════════════════════════════════════════════════════════════════════ */
    public function emiLedger()
    {
        return app(EmiController::class)->emiLedger();
    }

    public function storeEmiProvider(Request $request)
    {
        return app(EmiController::class)->storeEmiProvider($request);
    }

    public function recordEmiDeposit(Request $request)
    {
        return app(EmiController::class)->recordEmiDeposit($request);
    }

    public function updateEmiProvider(Request $request)
    {
        return app(EmiController::class)->updateEmiProvider($request);
    }

    /* ══════════════════════════════════════════════════════════════════════
       REPORTS & GST COMPLIANCE
       ══════════════════════════════════════════════════════════════════════ */
    public function reports(Request $request)
    {
        return app(ReportsController::class)->reports($request);
    }

    public function exportSalesCsv(Request $request)
    {
        return app(ReportsController::class)->exportSalesCsv($request);
    }

    public function exportGstCsv(Request $request)
    {
        return app(ReportsController::class)->exportGstCsv($request);
    }

    /* ══════════════════════════════════════════════════════════════════════
       MASTERS, SESSIONS & SECURITY OTP
       ══════════════════════════════════════════════════════════════════════ */
    public function masters()
    {
        return app(MastersController::class)->masters();
    }

    public function updateUserCredentials(Request $request, $id)
    {
        return app(MastersController::class)->updateUserCredentials($request, $id);
    }

    public function getLoginSessions(Request $request)
    {
        return app(MastersController::class)->getLoginSessions($request);
    }

    public function terminateLoginSession(Request $request, $id)
    {
        return app(MastersController::class)->terminateLoginSession($request, $id);
    }

    public function requestOtp(Request $request)
    {
        return app(MastersController::class)->requestOtp($request);
    }

    public function verifyOtpEndpoint(Request $request)
    {
        return app(MastersController::class)->verifyOtpEndpoint($request);
    }

    /* ══════════════════════════════════════════════════════════════════════
       PUBLIC STOREFRONT
       ══════════════════════════════════════════════════════════════════════ */
    public function publicLanding()
    {
        return app(PublicStoreController::class)->publicLanding();
    }

    public function publicStore(Request $request)
    {
        return app(PublicStoreController::class)->publicStore($request);
    }

    public function publicAbout()
    {
        return app(PublicStoreController::class)->publicAbout();
    }

    public function publicContact()
    {
        return app(PublicStoreController::class)->publicContact();
    }

    public function submitContact(Request $request)
    {
        return app(PublicStoreController::class)->submitContact($request);
    }

    public function publicTrackRepair(Request $request)
    {
        return app(PublicStoreController::class)->publicTrackRepair($request);
    }
}
