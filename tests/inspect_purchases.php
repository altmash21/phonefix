<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "ms_purchase_orders count: " . DB::table('ms_purchase_orders')->count() . PHP_EOL;
echo "ms_mobile_devices (new): " . DB::table('ms_mobile_devices')->where('type', 'new')->count() . PHP_EOL;
echo "ms_mobile_devices (second_hand): " . DB::table('ms_mobile_devices')->where('type', 'second_hand')->count() . PHP_EOL;
echo "ms_parts_inventory_history (addition): " . DB::table('ms_parts_inventory_history')->where('type', 'addition')->count() . PHP_EOL;

$pos = DB::table('ms_purchase_orders')->get();
foreach ($pos as $p) {
    echo "PO: #{$p->po_number} | Amount: {$p->total_amount} | Date: {$p->order_date}\n";
    $items = DB::table('ms_purchase_order_items')->where('purchase_order_id', $p->id)->get();
    foreach ($items as $item) {
        echo "   - Item: {$item->brand} {$item->model} {$item->variant} | Qty: {$item->qty} | Cost: {$item->unit_cost} | Total: {$item->line_total}\n";
    }
}

echo "\n--- Suppliers ---\n";
$suppliers = DB::table('ms_suppliers')->get();
foreach ($suppliers as $s) {
    echo "Supplier ID {$s->id}: {$s->name} | Phone: {$s->phone}\n";
}



