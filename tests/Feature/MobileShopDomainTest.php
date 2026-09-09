<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Repositories\MobileShop\MsStockRepository;
use App\DTOs\MobileShop\SaleInput;
use App\DTOs\MobileShop\StockInwardInput;
use App\DTOs\MobileShop\AccessoryVoidInput;
use App\Policies\MobileShop\MobileSalePolicy;
use App\Policies\MobileShop\MobileStockPolicy;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$passed = 0;
$failed = 0;

function check(string $label, bool $condition, &$passed, &$failed) {
    if ($condition) {
        echo " [PASS] $label\n";
        $passed++;
    } else {
        echo " [FAIL] $label\n";
        $failed++;
    }
}

echo "==========================================\n";
echo "MobiTrack Domain & Architecture Tests\n";
echo "==========================================\n\n";

// 1. MsStockRepository Tests
$repo = new MsStockRepository();
$company = DB::table('companies')->first();
$companyId = $company ? $company->id : 1;

$testImei = '999999999999999';
// Clean up if existed
DB::table('ms_mobile_devices')->where('imei_1', $testImei)->delete();

$devId = $repo->insertDevice([
    'company_id'      => $companyId,
    'brand'           => 'DomainTestBrand',
    'model'           => 'DomainTestModel',
    'imei_1'          => $testImei,
    'type'            => 'new',
    'purchase_cost'   => 10000,
    'selling_price'   => 12000,
    'status'          => 'in_stock',
    'condition_grade' => 'brand_new',
]);

check("MsStockRepository::insertDevice creates record", $devId > 0, $passed, $failed);

$foundByImei = $repo->findByImei($testImei, $companyId);
check("MsStockRepository::findByImei finds device", $foundByImei && $foundByImei->id === $devId, $passed, $failed);

$exists = $repo->existsByImei($testImei, $companyId);
check("MsStockRepository::existsByImei returns true", $exists === true, $passed, $failed);

$updated = $repo->updateStatus($devId, 'sold');
check("MsStockRepository::updateStatus marks device as sold", $updated === true, $passed, $failed);

$deleted = $repo->deleteDevice($devId, $companyId);
check("MsStockRepository::deleteDevice cleans up record", $deleted === true, $passed, $failed);

$notExists = $repo->existsByImei($testImei, $companyId);
check("MsStockRepository confirms device deleted", $notExists === false, $passed, $failed);

// 2. DTO Tests
$saleReq = Request::create('/sales', 'POST', [
    'customer_name' => 'John Doe',
    'customer_phone' => '9876543210',
    'payment_mode' => 'upi',
    'discount' => '250.50',
    'items' => [['id' => 1, 'qty' => 2]],
]);
$saleDto = SaleInput::fromRequest($saleReq, $companyId);
check("SaleInput DTO populates customerName correctly", $saleDto->customerName === 'John Doe', $passed, $failed);
check("SaleInput DTO casts discount as float", $saleDto->discount === 250.50, $passed, $failed);
check("SaleInput DTO populates paymentMode", $saleDto->paymentMode === 'upi', $passed, $failed);

$stockReq = Request::create('/stock', 'POST', [
    'brand' => 'Samsung',
    'model' => 'Galaxy S24',
    'imei_1' => '356789012345678',
    'purchase_cost' => '65000',
    'selling_price' => '72000',
]);
$stockDto = StockInwardInput::fromRequest($stockReq, $companyId);
check("StockInwardInput DTO populates brand and model", $stockDto->brand === 'Samsung' && $stockDto->model === 'Galaxy S24', $passed, $failed);
check("StockInwardInput DTO casts numeric prices", $stockDto->purchaseCost === 65000.0 && $stockDto->sellingPrice === 72000.0, $passed, $failed);

$voidReq = Request::create('/void', 'POST', ['reason' => 'Defective item']);
$voidDto = AccessoryVoidInput::fromRequest($voidReq, 42, $companyId);
check("AccessoryVoidInput DTO captures saleId and reason", $voidDto->saleId === 42 && $voidDto->reason === 'Defective item', $passed, $failed);

// 3. Laravel Policies Tests
$adminUser = User::where('email', 'admin@mobitrack.local')->first();
$salesUser = User::where('email', 'sales@mobitrack.local')->first();

$salePolicy = new MobileSalePolicy();
$stockPolicy = new MobileStockPolicy();

if ($adminUser) {
    check("Admin passes MobileSalePolicy::create", $salePolicy->create($adminUser) === true, $passed, $failed);
    check("Admin passes MobileSalePolicy::void", $salePolicy->void($adminUser) === true, $passed, $failed);
    check("Admin passes MobileStockPolicy::delete", $stockPolicy->delete($adminUser) === true, $passed, $failed);
}

if ($salesUser) {
    check("Sales user passes MobileSalePolicy::viewAny", $salePolicy->viewAny($salesUser) === true, $passed, $failed);
    check("Sales user passes MobileSalePolicy::create", $salePolicy->create($salesUser) === true, $passed, $failed);
}

// 4. Trait Decomposition Sanity Check
$showComponent = new class extends \App\Abstracts\View\Components\Documents\Show {
    public function __construct() {}
    public function render() { return ''; }
};
check("Documents\Show instantiates and includes HasShowRoutesAndButtons", method_exists($showComponent, 'getPrintRoute'), $passed, $failed);

$formComponent = new class extends \App\Abstracts\View\Components\Documents\Form {
    public function __construct() {}
    public function render() { return ''; }
};
check("Documents\Form instantiates and includes HasFormDatesAndNumbers", method_exists($formComponent, 'getIssuedAt'), $passed, $failed);

echo "\n==========================================\n";
echo "Total Checks: " . ($passed + $failed) . " | Passed: $passed | Failed: $failed\n";
echo "==========================================\n";

exit($failed > 0 ? 1 : 0);
