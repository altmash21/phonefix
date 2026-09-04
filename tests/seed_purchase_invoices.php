<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$companyId = 1;

// 1. Check if Accessories Wholesale Invoice exists
$hasPartsInvoice = DB::table('ms_purchase_orders')->where('po_number', 'INV-2026-8942')->exists();
if (!$hasPartsInvoice) {
    $poId = DB::table('ms_purchase_orders')->insertGetId([
        'company_id'   => $companyId,
        'supplier_id'  => 2, // National Screen & Spare Parts Hub
        'po_number'    => 'INV-2026-8942',
        'order_date'   => '2026-08-28',
        'tax_type'     => 'intra_state',
        'subtotal'     => 26970.00,
        'cgst_amount'  => 2427.30,
        'sgst_amount'  => 2427.30,
        'igst_amount'  => 0,
        'total_amount' => 31824.60,
        'amount_paid'  => 31824.60,
        'balance_due'  => 0.00,
        'status'       => 'received',
        'created_by'   => 1,
        'created_at'   => '2026-08-28 11:30:00',
        'updated_at'   => '2026-08-28 11:30:00',
    ]);

    $items = [
        ['brand' => 'Apple', 'model' => '9D Super Clear Tempered Glass (iPhone 14/15)', 'variant' => 'tempered_glass', 'hsn_code' => '85177090', 'qty' => 50, 'unit_cost' => 22.00, 'tax_rate' => 18.00, 'line_total' => 1100.00],
        ['brand' => 'Universal', 'model' => 'Matte Smoke Anti-Drop Bumper Case (Universal)', 'variant' => 'back_cover_case', 'hsn_code' => '85177090', 'qty' => 25, 'unit_cost' => 45.00, 'tax_rate' => 18.00, 'line_total' => 1125.00],
        ['brand' => 'Apple', 'model' => 'Original OLED Display Screen Folder (iPhone 14)', 'variant' => 'display_folder', 'hsn_code' => '85177090', 'qty' => 5, 'unit_cost' => 1650.00, 'tax_rate' => 18.00, 'line_total' => 8250.00],
        ['brand' => 'Samsung', 'model' => 'Samsung Galaxy A54 Front Outer Glass with OCA', 'variant' => 'front_glass', 'hsn_code' => '85177090', 'qty' => 15, 'unit_cost' => 110.00, 'tax_rate' => 18.00, 'line_total' => 1650.00],
        ['brand' => 'Universal', 'model' => 'Type-C 65W Braided Fast Charging Cable (1.5m)', 'variant' => 'cables', 'hsn_code' => '85177090', 'qty' => 30, 'unit_cost' => 38.00, 'tax_rate' => 18.00, 'line_total' => 1140.00],
        ['brand' => 'Universal', 'model' => 'Universal Type-C Charging Pin Connector Jack', 'variant' => 'charging_pin', 'hsn_code' => '85177090', 'qty' => 40, 'unit_cost' => 12.00, 'tax_rate' => 18.00, 'line_total' => 480.00],
        ['brand' => 'Xiaomi', 'model' => 'High Capacity 5000mAh Battery (Redmi Note 12)', 'variant' => 'battery', 'hsn_code' => '85177090', 'qty' => 8, 'unit_cost' => 340.00, 'tax_rate' => 18.00, 'line_total' => 2720.00],
    ];

    foreach ($items as $it) {
        $it['purchase_order_id'] = $poId;
        $it['qty_received'] = $it['qty'];
        DB::table('ms_purchase_order_items')->insert($it);
    }
    echo "Created INV-2026-8942 with " . count($items) . " items.\n";
}

// 2. Check if Pre-Owned Buyback Invoice exists
$hasBuybackInvoice = DB::table('ms_purchase_orders')->where('po_number', 'BUYBACK-2026-0001')->exists();
if (!$hasBuybackInvoice) {
    // Create customer supplier for buybacks
    $buybackSupplier = DB::table('ms_suppliers')->where('name', 'Walk-in Customer Buybacks')->first();
    if (!$buybackSupplier) {
        $suppId = DB::table('ms_suppliers')->insertGetId([
            'company_id' => $companyId,
            'name'       => 'Walk-in Customer Buybacks',
            'phone'      => '9876500112',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } else {
        $suppId = $buybackSupplier->id;
    }

    $poId = DB::table('ms_purchase_orders')->insertGetId([
        'company_id'   => $companyId,
        'supplier_id'  => $suppId,
        'po_number'    => 'BUYBACK-2026-0001',
        'order_date'   => '2026-08-31',
        'tax_type'     => 'intra_state',
        'subtotal'     => 42000.00,
        'cgst_amount'  => 0,
        'sgst_amount'  => 0,
        'igst_amount'  => 0,
        'total_amount' => 42000.00,
        'amount_paid'  => 42000.00,
        'balance_due'  => 0.00,
        'status'       => 'received',
        'created_by'   => 1,
        'created_at'   => '2026-08-31 15:40:00',
        'updated_at'   => '2026-08-31 15:40:00',
    ]);

    DB::table('ms_purchase_order_items')->insert([
        'purchase_order_id' => $poId,
        'brand'             => 'Apple',
        'model'             => 'iPhone 13 Pro (128GB Sierra Blue)',
        'variant'           => 'Pre-Owned Grade A [IMEI: 352948102948192]',
        'hsn_code'          => '85171300',
        'qty'               => 1,
        'qty_received'      => 1,
        'unit_cost'         => 42000.00,
        'tax_rate'          => 0,
        'line_total'        => 42000.00,
    ]);
    echo "Created BUYBACK-2026-0001.\n";
}

echo "Done!\n";
