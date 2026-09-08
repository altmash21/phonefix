<?php

use Illuminate\Support\Facades\Route;

/**
 * 'admin' middleware applied to all routes
 */

Route::group(['prefix' => 'common'], function () {
    Route::get('companies/autocomplete', 'Common\\Companies@autocomplete')->name('companies.autocomplete');
    Route::get('companies/{company}/switch', 'Common\\Companies@switch')->name('companies.switch');
    Route::get('companies/{company}/enable', 'Common\\Companies@enable')->name('companies.enable');
    Route::get('companies/{company}/disable', 'Common\\Companies@disable')->name('companies.disable');
    Route::resource('companies', 'Common\\Companies', ['middleware' => ['dropzone']]);

    Route::get('contacts/index', 'Common\\Contacts@index')->name('contacts.index');
});

Route::group(['prefix' => 'auth'], function () {
    Route::get('logout', 'Auth\\Login@destroy')->name('logout');

    Route::get('users/autocomplete', 'Auth\\Users@autocomplete')->name('users.autocomplete');
    Route::get('users/landingpages', 'Auth\\Users@landingPages')->name('users.landingpages');
    Route::get('users/{user}/enable', 'Auth\\Users@enable')->name('users.enable');
    Route::get('users/{user}/disable', 'Auth\\Users@disable')->name('users.disable');
    Route::get('users/{user}/invite', 'Auth\\Users@invite')->name('users.invite');
    Route::resource('users', 'Auth\\Users', ['middleware' => ['dropzone']]);

    Route::get('profile/{user}/edit', 'Auth\\Users@edit')->name('profile.edit');
    Route::patch('profile/{user}', 'Auth\\Users@update')->middleware('dropzone')->name('profile.update');
});

Route::group(['prefix' => 'settings'], function () {
    Route::group(['as' => 'settings.'], function () {
        Route::get('company', 'Settings\\Company@edit')->name('company.edit');
        Route::patch('company', 'Settings\\Company@update')->middleware('dropzone')->name('company.update');
        Route::get('localisation', 'Settings\\Localisation@edit')->name('localisation.edit');
        Route::patch('localisation', 'Settings\\Localisation@update')->name('localisation.update');
    });
});

Route::group(['as' => 'modals.', 'prefix' => 'modals'], function () {
    Route::resource('companies', 'Modals\\Companies');
    Route::resource('taxes', 'Modals\\Taxes');
});

// ==========================================
// MobiTrack — Mobile Shop ERP Dedicated Routes
// All routes require 'read-mobileshop-dashboard' as a baseline.
// Fine-grained niche scoping is handled in the controller.
// ==========================================
Route::group(['as' => 'mobileshop.', 'prefix' => 'mobileshop'], function () {

    // ── CORE 5-HUB NAVIGATION (all roles land here, content niche-scoped in controller) ──
    Route::get('/', 'MobileShopController@dashboard')
        ->middleware('permission:read-mobileshop-dashboard')
        ->name('dashboard');

    Route::get('purchase', 'MobileShopController@purchaseHub')
        ->middleware('permission:read-mobileshop-purchase')
        ->name('purchase');

    Route::get('sales', 'MobileShopController@salesHub')
        ->middleware('permission:read-mobileshop-sales')
        ->name('sales');

    Route::get('stock', 'MobileShopController@stockHub')
        ->middleware('permission:read-mobileshop-stock')
        ->name('stock');

    Route::get('reports', 'MobileShopController@reports')
        ->middleware('permission:read-mobileshop-reports|read-reports-khata|read-reports-financial')
        ->name('reports');

    Route::get('masters', 'MobileShopController@masters')
        ->middleware('permission:read-mobileshop-masters')
        ->name('masters');
    Route::post('masters/user/{id}/update', 'MobileShopController@updateUserCredentials')
        ->middleware('permission:read-mobileshop-masters')
        ->name('masters.user.update');

    // ── LOGIN SESSION MANAGEMENT (Admin only) ──
    Route::get('sessions', 'MobileShopController@getLoginSessions')
        ->middleware('permission:read-mobileshop-masters')
        ->name('sessions.index');
    Route::post('sessions/{id}/terminate', 'MobileShopController@terminateLoginSession')
        ->middleware('permission:read-mobileshop-masters')
        ->name('sessions.terminate');

    // ── OTP VERIFICATION (Security) ──
    Route::post('otp/request', 'MobileShopController@requestOtp')
        ->name('otp.request');
    Route::post('otp/verify', 'MobileShopController@verifyOtpEndpoint')
        ->name('otp.verify');

    // ── FULL-PAGE REGISTRATION (Sale / Purchase / EMI Ledger) ──
    Route::get('purchase/create', 'MobileShopController@purchaseCreate')
        ->middleware('permission:read-mobileshop-purchase')
        ->name('purchase.create');
    Route::post('purchase/store-bulk', 'MobileShopController@storeBulkPurchase')
        ->middleware('permission:create-purchase-phones|create-purchase-secondhand|create-purchase-accessories|create-purchase-covers')
        ->name('purchase.store_bulk');
    Route::get('sales/create', 'MobileShopController@saleCreate')
        ->middleware('permission:read-mobileshop-sales')
        ->name('sales.create');
    Route::post('sales/store-multi', 'MobileShopController@storeMultiSale')
        ->middleware('permission:create-sale-phones|create-sale-secondhand|create-sale-accessories|create-sale-covers')
        ->name('sales.store_multi');
    Route::get('emi-ledger', 'MobileShopController@emiLedger')
        ->middleware('permission:read-mobileshop-sales|read-mobileshop-purchase')
        ->name('emi.ledger');
    Route::post('emi-ledger/provider', 'MobileShopController@storeEmiProvider')
        ->middleware('permission:read-mobileshop-sales|read-mobileshop-purchase')
        ->name('emi.provider.store');
    Route::post('emi-ledger/deposit', 'MobileShopController@recordEmiDeposit')
        ->middleware('permission:read-mobileshop-sales|read-mobileshop-purchase')
        ->name('emi.deposit');
    Route::post('emi-ledger/provider/update', 'MobileShopController@updateEmiProvider')
        ->middleware('permission:read-mobileshop-sales|read-mobileshop-purchase')
        ->name('emi.provider.update');

    // ── PURCHASE ACTIONS (niche-gated) ──
    Route::post('purchase/store', 'MobileShopController@storePurchase')
        ->middleware('permission:create-purchase-phones|create-purchase-secondhand|create-purchase-accessories|create-purchase-covers')
        ->name('purchase.store');

    // ── SALES ACTIONS (niche-gated) ──
    Route::post('sales/store', 'MobileShopController@storeSale')
        ->middleware('permission:create-sale-phones|create-sale-secondhand|create-sale-accessories|create-sale-covers')
        ->name('sales.store');

    // ── STOCK ACTIONS (niche-gated) ──
    Route::post('stock/store', 'MobileShopController@storeStock')
        ->middleware('permission:manage-stock-phones|manage-stock-secondhand|manage-stock-accessories|manage-stock-covers|manage-stock-repairs')
        ->name('stock.store');

    Route::post('stock/{id}/update', 'MobileShopController@updateStock')
        ->middleware('permission:manage-stock-phones|manage-stock-secondhand|manage-stock-accessories|manage-stock-covers|manage-stock-repairs')
        ->name('stock.update');

    Route::get('stock/history/{type}/{id}', 'MobileShopController@getStockHistory')
        ->middleware('permission:read-mobileshop-stock|read-mobileshop-accessories|read-mobileshop-reports')
        ->name('stock.history');

    Route::post('stock/delete', 'MobileShopController@deleteStockItem')
        ->middleware('permission:manage-stock-phones|manage-stock-secondhand|manage-stock-accessories|manage-stock-covers|manage-stock-repairs')
        ->name('stock.delete');

    // ── INVOICE / RECEIPT VIEWER & PDF EXPORT ──
    Route::get('invoice/{id}', 'MobileShopController@invoice')
        ->middleware('permission:read-mobileshop-sales')
        ->name('invoice');
    Route::get('invoice/{id}/pdf', 'MobileShopController@invoicePdf')
        ->middleware('permission:read-mobileshop-sales')
        ->name('invoice.pdf');

    Route::get('invoice/purchase/{id}', 'MobileShopController@purchaseInvoice')
        ->middleware('permission:read-mobileshop-purchase')
        ->name('purchase.invoice');
    Route::get('invoice/purchase/{id}/pdf', 'MobileShopController@purchaseInvoicePdf')
        ->middleware('permission:read-mobileshop-purchase')
        ->name('purchase.invoice.pdf');

    // ── REPORTS ACTIONS ──
    Route::get('reports/export/sales', 'MobileShopController@exportSalesCsv')
        ->middleware('permission:read-mobileshop-reports|read-reports-financial')
        ->name('reports.export.sales');

    Route::get('reports/export/gst', 'MobileShopController@exportGstCsv')
        ->middleware('permission:read-mobileshop-reports|read-reports-financial')
        ->name('reports.export.gst');

    // ── VOID (Admin Only) ──
    Route::post('sales/{id}/void', 'MobileShopController@voidMobileSale')
        ->middleware('permission:void-mobileshop-sales')
        ->name('sales.void');

    // ── LEGACY PANEL ROUTES (kept for backward compat with existing views/links) ──
    Route::get('pos', 'MobileShopController@pos')
        ->middleware('permission:read-mobileshop-pos')
        ->name('pos');
    Route::post('pos/sale', 'MobileShopController@processSale')
        ->middleware('permission:create-mobileshop-pos')
        ->name('pos.sale');
    Route::post('pos/scan-emi-bill', 'MobileShopController@scanEmiBill')
        ->middleware('permission:read-mobileshop-pos|create-mobileshop-pos')
        ->name('pos.scan_emi_bill');
    Route::get('new-mobiles', 'MobileShopController@newMobiles')
        ->middleware('permission:read-mobileshop-new')
        ->name('new_mobiles');
    Route::post('new-mobiles/store', 'MobileShopController@storeNewMobile')
        ->middleware('permission:create-mobileshop-pos|read-mobileshop-new')
        ->name('new_mobiles.store');
    Route::get('second-hand', 'MobileShopController@secondHand')
        ->middleware('permission:read-mobileshop-secondhand')
        ->name('second_hand');
    Route::post('second-hand/buyback', 'MobileShopController@storeSecondHand')
        ->middleware('permission:create-mobileshop-secondhand')
        ->name('second_hand.buyback');
    Route::post('second-hand/sale', 'MobileShopController@sellSecondHand')
        ->middleware('permission:sell-mobileshop-secondhand')
        ->name('second_hand.sale');

    Route::get('accessories/purchase', 'MobileShopController@accessoriesPurchase')
        ->middleware('permission:create-mobileshop-accessories|read-mobileshop-accessories|create-purchase-accessories|create-purchase-covers')
        ->name('accessories.purchase');
    Route::post('accessories/store', 'MobileShopController@storePart')
        ->middleware('permission:create-mobileshop-accessories')
        ->name('accessories.store');
    Route::post('accessories/bulk-restock', 'MobileShopController@bulkRestock')
        ->middleware('permission:create-mobileshop-accessories')
        ->name('accessories.bulk_restock');
    Route::post('accessories/sale', 'MobileShopController@sellAccessory')
        ->middleware('permission:sell-mobileshop-accessories')
        ->name('accessories.sale');
    Route::get('accessories/invoice/{id}', 'MobileShopController@accessoryInvoice')
        ->middleware('permission:read-mobileshop-accessories')
        ->name('accessories.invoice');
    Route::get('accessories/invoice/{id}/pdf', 'MobileShopController@accessoryInvoicePdf')
        ->middleware('permission:read-mobileshop-accessories')
        ->name('accessories.invoice.pdf');
    Route::get('accessories/{id}/history', 'MobileShopController@getPartHistory')
        ->middleware('permission:read-mobileshop-accessories')
        ->name('accessories.history');
    Route::get('accessories-categories', 'MobileShopController@getCategories')
        ->middleware('permission:read-mobileshop-accessories')
        ->name('accessories.categories');
    Route::post('accessories-categories', 'MobileShopController@storeCategory')
        ->middleware('permission:create-mobileshop-accessories')
        ->name('accessories.categories.store');
    Route::delete('accessories-categories/{id}', 'MobileShopController@deleteCategory')
        ->middleware('permission:create-mobileshop-accessories')
        ->name('accessories.categories.delete');
    Route::post('accessories/{id}/void', 'MobileShopController@voidAccessorySale')
        ->middleware('permission:void-mobileshop-sales|read-mobileshop-sales|sell-mobileshop-accessories|create-sale-accessories')
        ->name('accessories.void');
    Route::get('khata', 'MobileShopController@khata')
        ->middleware('permission:read-mobileshop-khata|read-mobileshop-sales|read-mobileshop-accessories|read-mobileshop-repairs|read-mobileshop-dashboard')
        ->name('khata');
    Route::post('khata/collect', 'MobileShopController@collectKhata')
        ->middleware('permission:create-mobileshop-khata|read-mobileshop-khata|read-mobileshop-sales|read-mobileshop-accessories|read-mobileshop-repairs|create-sale-accessories|create-mobileshop-pos')
        ->name('khata.collect');
    Route::get('khata/customer/{id}/statement', 'MobileShopController@customerStatement')
        ->middleware('permission:read-mobileshop-khata|read-mobileshop-sales|read-mobileshop-accessories|read-mobileshop-repairs|read-mobileshop-dashboard')
        ->name('khata.customer_statement');
    Route::get('khata/customer/{id}/statement/pdf', 'MobileShopController@customerStatementPdf')
        ->middleware('permission:read-mobileshop-khata|read-mobileshop-sales|read-mobileshop-accessories|read-mobileshop-repairs|read-mobileshop-dashboard')
        ->name('khata.customer_statement_pdf');
    Route::get('purchase-orders', 'MobileShopController@purchaseOrders')
        ->middleware('permission:read-mobileshop-procurement')
        ->name('purchase_orders');
    Route::post('purchase-orders/payment', 'MobileShopController@recordSupplierPayment')
        ->middleware('permission:create-mobileshop-procurement')
        ->name('purchase_orders.payment');
    Route::post('supplier/update', 'MobileShopController@updateSupplier')
        ->middleware('permission:read-mobileshop-procurement|create-mobileshop-procurement')
        ->name('supplier.update');
    Route::get('repairs', 'MobileShopController@repairs')
        ->middleware('permission:read-mobileshop-repairs')
        ->name('repairs');
    Route::post('repairs/store', 'MobileShopController@storeRepair')
        ->middleware('permission:update-mobileshop-repairs|read-mobileshop-repairs')
        ->name('repairs.store');
    Route::post('repairs/{id}/update', 'MobileShopController@updateRepairStatus')
        ->middleware('permission:update-mobileshop-repairs')
        ->name('repairs.update');

    Route::get('parts/search', 'MobileShopController@searchParts')
        ->middleware('permission:read-mobileshop-repairs|read-mobileshop-accessories')
        ->name('parts.search');
});
