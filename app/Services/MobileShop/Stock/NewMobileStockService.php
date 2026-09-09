<?php

namespace App\Services\MobileShop\Stock;

use App\Repositories\MobileShop\Contracts\MsStockRepositoryInterface;
use App\Repositories\MobileShop\MsStockRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewMobileStockService
{
    protected MsStockRepositoryInterface $stockRepo;

    public function __construct(?MsStockRepositoryInterface $stockRepo = null)
    {
        $this->stockRepo = $stockRepo ?? app(MsStockRepositoryInterface::class);
    }

    /**
     * Store Brand New Mobile into Inventory (with IMEI Uniqueness Check)
     */
    public function store(Request $request, int $companyId, ?string $photoPath): array
    {
        // IMEI Uniqueness check within company using Repository
        $exists = $this->stockRepo->existsByImei($request->imei_1, $companyId);

        if ($exists) {
            return [
                'success' => false,
                'error' => "A mobile device with IMEI {$request->imei_1} is already registered in inventory!",
            ];
        }

        $supplier = DB::table('ms_suppliers')->where('company_id', $companyId)->first();
        $supplierId = $supplier ? $supplier->id : 1;
        $poNum = 'PO-PHONES-' . date('Ymd') . '-' . rand(100, 999);
        $phoneCost = (float) $request->purchase_cost;

        $poId = DB::table('ms_purchase_orders')->insertGetId([
            'company_id'   => $companyId,
            'supplier_id'  => $supplierId,
            'po_number'    => $poNum,
            'order_date'   => now()->toDateString(),
            'tax_type'     => 'intra_state',
            'subtotal'     => $phoneCost,
            'total_amount' => $phoneCost,
            'amount_paid'  => $phoneCost,
            'balance_due'  => 0,
            'status'       => 'received',
            'created_by'   => auth()->id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('ms_purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'brand'             => $request->brand,
            'model'             => $request->model,
            'variant'           => ($request->ram ? $request->ram . '/' . $request->storage : '') . ($request->color ? ' (' . $request->color . ')' : '') . " [IMEI: {$request->imei_1}]",
            'hsn_code'          => '85171300',
            'qty'               => 1,
            'qty_received'      => 1,
            'unit_cost'         => $phoneCost,
            'tax_rate'          => 18.00,
            'line_total'        => $phoneCost,
        ]);

        $this->stockRepo->insertDevice([
            'company_id' => $companyId,
            'purchase_order_id' => $poId,
            'type' => 'new',
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color ?? 'Standard',
            'ram' => $request->ram,
            'storage' => $request->storage,
            'imei_1' => $request->imei_1,
            'imei_2' => $request->imei_2,
            'purchase_cost' => $phoneCost,
            'selling_price' => (float) $request->selling_price,
            'photo_path' => $photoPath,
            'box_photo_path' => $photoPath,
            'status' => 'in_stock',
            'condition_grade' => 'brand_new',
        ]);

        return [
            'success' => true,
            'po_number' => $poNum,
            'brand' => $request->brand,
            'model' => $request->model,
            'imei_1' => $request->imei_1,
        ];
    }
}
