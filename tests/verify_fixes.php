<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "==========================================\n";
echo "MobiTrack ERP Comprehensive Fix Verification\n";
echo "==========================================\n\n";

$passed = 0;
$failed = 0;

function assertCheck($desc, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] $desc\n";
        $passed++;
    } else {
        echo " [FAIL] $desc\n";
        $failed++;
    }
}

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\User;
use App\Models\Common\Company;
use App\Models\Banking\Transaction;

// 1. Verify 5-Hub Routes Exist and Resolve to Controller Methods
$routesToCheck = [
    'mobileshop.dashboard' => 'dashboard',
    'mobileshop.sales' => 'salesHub',
    'mobileshop.purchase' => 'purchaseHub',
    'mobileshop.stock' => 'stockHub',
    'mobileshop.reports' => 'reports',
    'mobileshop.masters' => 'masters',
    'mobileshop.purchase.store' => 'storePurchase',
    'mobileshop.sales.store' => 'storeSale',
    'mobileshop.stock.store' => 'storeStock',
    'mobileshop.stock.update' => 'updateStock',
    'mobileshop.purchase.invoice' => 'purchaseInvoiceView',
];

foreach ($routesToCheck as $routeName => $method) {
    assertCheck("Route '$routeName' exists", Route::has($routeName));
}

// 2. Verify Admin & Settings Menu Generation (with authenticated admin user)
$adminUser = User::where('email', 'admin@mobitrack.local')->first();
assertCheck("Admin user exists (admin@mobitrack.local)", $adminUser !== null);

if ($adminUser) {
    auth()->setUser($adminUser);
    session(['company_id' => 1]);

    try {
        menu()->create('admin', function ($adminMenu) {
            $menuEvent = new \App\Events\Menu\AdminCreated($adminMenu);
            $listener = new \App\Listeners\Menu\ShowInAdmin();
            $listener->handle($menuEvent);
        });
        assertCheck("ShowInAdmin listener handles menu event cleanly for Admin", true);
    } catch (\Throwable $e) {
        assertCheck("ShowInAdmin listener handles menu event cleanly: " . $e->getMessage(), false);
    }

    try {
        menu()->create('settings', function ($settingsMenu) {
            $settingsEvent = new \App\Events\Menu\SettingsCreated($settingsMenu);
            $listener = new \App\Listeners\Menu\ShowInSettings();
            $listener->handle($settingsEvent);
        });
        assertCheck("ShowInSettings listener handles menu event cleanly for Admin", true);
    } catch (\Throwable $e) {
        assertCheck("ShowInSettings listener handles menu event cleanly: " . $e->getMessage(), false);
    }
}

// 3. Verify Model Relations Cleanliness (No references to deleted Document/Transfer/Recurring)
$company = Company::first();
assertCheck("Company model instantiates cleanly", $company !== null);
if ($company) {
    assertCheck("Company::categories() works", $company->categories() instanceof \Illuminate\Database\Eloquent\Relations\HasMany);
    assertCheck("Company::transactions() works", $company->transactions() instanceof \Illuminate\Database\Eloquent\Relations\HasMany);
}

// 4. Verify Database Schema Enums & Constraints
$pfx = DB::getTablePrefix();
$salesColumns = DB::select("SHOW COLUMNS FROM `{$pfx}ms_mobile_sales` WHERE Field = 'status'");
if (!empty($salesColumns)) {
    assertCheck("ms_mobile_sales.status enum contains 'voided'", str_contains($salesColumns[0]->Type, "'voided'"));
}

$partsColumns = DB::select("SHOW COLUMNS FROM `{$pfx}ms_parts_inventory` WHERE Field = 'category'");
if (!empty($partsColumns)) {
    assertCheck("ms_parts_inventory.category is varchar", str_starts_with(strtolower($partsColumns[0]->Type), 'varchar'));
}

// 5. Verify RBAC User Accounts & Seeded Data
$adminUser = User::where('email', 'admin@mobitrack.local')->first();
assertCheck("Admin user exists (admin@mobitrack.local)", $adminUser !== null);

$salesUser = User::where('email', 'sales@mobitrack.local')->first();
assertCheck("Sales user exists (sales@mobitrack.local)", $salesUser !== null);

$techUser = User::where('email', 'tech@mobitrack.local')->first();
assertCheck("Tech user exists (tech@mobitrack.local)", $techUser !== null);

$customerCount = DB::table('ms_customers')->where('company_id', 1)->count();
assertCheck("Customer database seeded ($customerCount found)", $customerCount > 0);

$partsCount = DB::table('ms_parts_inventory')->where('company_id', 1)->count();
assertCheck("Parts catalog seeded ($partsCount found)", $partsCount > 0);

// 6. Verify Niche Scoped View Rendering for Staff Roles
\Illuminate\Support\Facades\URL::defaults(['company_id' => 1]);
$staffRoles = [
    'sales@mobitrack.local' => ['pos_visible' => true, 'acc_visible' => false],
    'accessories@mobitrack.local' => ['pos_visible' => false, 'acc_visible' => true],
];

foreach ($staffRoles as $email => $expected) {
    $u = User::where('email', $email)->first();
    if ($u) {
        auth()->setUser($u);
        session(['company_id' => 1]);
        try {
            $controller = app(\App\Http\Controllers\MobileShopController::class);
            $salesView = $controller->salesHub(request())->render();
            
            if ($expected['pos_visible']) {
                assertCheck("$email sees Register Sale button", str_contains($salesView, 'Register Sale'));
                assertCheck("$email does NOT see Add Sales button", !str_contains($salesView, 'Add Sales'));
            } else {
                assertCheck("$email sees Add Sales button", str_contains($salesView, 'Add Sales'));
                assertCheck("$email does NOT see Register Sale button", !str_contains($salesView, 'Register Sale'));
            }

            $khataView = $controller->khata()->render();
            assertCheck("Khata view renders cleanly without errors for $email", str_contains($khataView, 'Customer Khata & Credit Ledger'));
            
            $purchaseView = $controller->purchaseHub(request())->render();
            assertCheck("PurchaseHub renders cleanly without syntax errors for $email", strlen($purchaseView) > 0);

            if ($u->can('read-mobileshop-procurement') || $u->hasRole('admin') || $u->hasRole('store-admin') || $u->hasRole('sales-staff')) {
                $poRes = $controller->purchaseOrders();
                if ($poRes instanceof \Illuminate\Http\RedirectResponse) {
                    assertCheck("purchaseOrders redirects to purchase hub for $email", $poRes->isRedirect());
                } else {
                    $poView = $poRes->render();
                    assertCheck("purchaseOrders renders cleanly with supplier wallets for $email", str_contains($poView, 'Prepaid Wallet:'));
                }
            }
        } catch (\Throwable $e) {
            assertCheck("Render check for $email: " . $e->getMessage(), false);
        }
    }
}

// 7. Verify Bulk Purchase and AI Invoice Photo Scanner Views
try {
    auth()->loginUsingId(1);
    session(['company_id' => 1]);
    $accCtrl = app(\App\Http\Controllers\MobileShop\AccessoriesController::class);
    $accRes = $accCtrl->accessoriesPurchase(request());
    $accView = $accRes->render();
    assertCheck("Bulk Accessories & AI Invoice Scanner renders cleanly", str_contains($accView, 'AI Invoice Scanner & OCR Intake') && str_contains($accView, 'Upload Invoice Photo'));

    $purCtrl = app(\App\Http\Controllers\MobileShop\PurchaseController::class);
    $purRes = $purCtrl->purchaseCreate(request());
    $purView = $purRes->render();
    assertCheck("Bulk Phones Purchase Inward renders cleanly", str_contains($purView, 'Stock Items (Bulk)') && str_contains($purView, 'bulkPurchaseForm'));

    $repairsCtrl = app(\App\Http\Controllers\MobileShop\RepairsController::class);
    $repairsRes = $repairsCtrl->repairs(request());
    $repairsView = $repairsRes->render();
    assertCheck("Repairs view renders cleanly without route or syntax errors", strlen($repairsView) > 0);

    $mastersCtrl = app(\App\Http\Controllers\MobileShop\MastersController::class);
    $mastersRes = $mastersCtrl->masters();
    $mastersView = $mastersRes->render();
    assertCheck("Masters Hub renders cleanly without lazy loading or route errors", str_contains($mastersView, 'Parts & Accessories Categories') && str_contains($mastersView, 'Suppliers & Credit Wallets') && str_contains($mastersView, 'Staff Counter Users & Roles'));

    // 8. Verify Apple Design System Public Pages
    $pubCtrl = app(\App\Http\Controllers\MobileShop\PublicStoreController::class);

    $homeView = $pubCtrl->publicLanding()->render();
    assertCheck("Public Landing renders with Apple Design tokens (hero display & product shadow)", 
        str_contains($homeView, 'Titanium. So strong. So light.') && 
        str_contains($homeView, 'apple-product-shadow') && 
        str_contains($homeView, '#0066cc'));

    $shopView = $pubCtrl->publicStore(request())->render();
    assertCheck("Public Store renders with Apple catalog cards and pill search", 
        str_contains($shopView, 'The finest technology, verified.') && 
        str_contains($shopView, 'apple-utility-card'));

    $firstDevice = DB::table('ms_mobile_devices')->first();
    if ($firstDevice) {
        $prodView = $pubCtrl->publicProductDetail($firstDevice->id)->render();
        assertCheck("Public Product Detail renders with Apple buy configurator and sticky bar", 
            str_contains($prodView, 'Inclusive of all taxes') && 
            str_contains($prodView, 'apple-product-shadow'));
    }

    $repairView = $pubCtrl->publicTrackRepair(request())->render();
    assertCheck("Public Track Repair renders with Apple support layout", 
        str_contains($repairView, 'Track your repair live.'));

    $aboutView = $pubCtrl->publicAbout()->render();
    assertCheck("Public About renders with Apple environment editorial story", 
        str_contains($aboutView, 'The most sustainable phone is the one that lasts.'));

    $contactView = $pubCtrl->publicContact()->render();
    assertCheck("Public Contact renders with Apple showroom card layout", 
        str_contains($contactView, 'Visit our showroom or connect with our desk.'));

} catch (\Throwable $e) {
    assertCheck("View rendering check: " . $e->getMessage(), false);
}

echo "\n==========================================\n";
echo "Total Checks: " . ($passed + $failed) . " | Passed: $passed | Failed: $failed\n";
echo "==========================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
