<?php

namespace App\Services\MobileShop\Stock;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockHistoryService
{
    /**
     * Build comprehensive audit trail for a spare part or phone device.
     */
    public function getHistory(string $type, int $id, int $companyId): array
    {
        $history = [];
        $itemInfo = [];

        if ($type === 'part') {
            $part = DB::table('ms_parts_inventory')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->first();

            if (!$part) {
                return ['success' => false, 'message' => 'Part not found in inventory', 'status_code' => 404];
            }

            $itemInfo = [
                'id' => $part->id,
                'name' => $part->name,
                'type' => 'part',
                'type_label' => 'Spare Part / Accessory',
                'category' => ucwords(str_replace('_', ' ', $part->category)),
                'sku' => $part->compatible_model ?: 'Universal',
                'current_stock' => $part->stock_qty . ' units',
                'stock_qty' => (int) $part->stock_qty,
                'unit_cost' => '₹' . number_format($part->unit_cost, 2),
                'selling_price' => '₹' . number_format($part->selling_price, 2),
                'is_in_stock' => $part->stock_qty > 0,
            ];

            // 1. Fetch from ms_parts_inventory_history
            $partHistory = DB::table('ms_parts_inventory_history')
                ->leftJoin('users', 'ms_parts_inventory_history.user_id', '=', 'users.id')
                ->select('ms_parts_inventory_history.*', 'users.name as user_name')
                ->where('ms_parts_inventory_history.part_id', $id)
                ->orderBy('ms_parts_inventory_history.id', 'desc')
                ->get();

            foreach ($partHistory as $h) {
                $ref = $h->reference ?? '';
                $isAddition = ($h->type === 'addition');
                $isDeletion = (stripos($ref, 'deletion') !== false || stripos($ref, 'reduction') !== false || stripos($ref, 'damaged') !== false || stripos($ref, 'write-off') !== false);
                $isRepair = (stripos($ref, 'repair') !== false || stripos($ref, 'job') !== false);

                if ($isAddition) {
                    $actionType = 'added';
                    $actionLabel = 'Stock Inward / Added';
                    $badgeClass = 'badge-green';
                    $qtyDisplay = '+' . abs($h->quantity) . ' Units';
                } elseif ($isDeletion) {
                    $actionType = 'deleted';
                    $actionLabel = 'Stock Deleted / Reduced';
                    $badgeClass = 'badge-red';
                    $qtyDisplay = '-' . abs($h->quantity) . ' Units';
                } elseif ($isRepair) {
                    $actionType = 'repair';
                    $actionLabel = 'Consumed in Repair';
                    $badgeClass = 'badge-purple';
                    $qtyDisplay = '-' . abs($h->quantity) . ' Units';
                } else {
                    $actionType = 'sold';
                    $actionLabel = 'Sold to Customer';
                    $badgeClass = 'badge-blue';
                    $qtyDisplay = '-' . abs($h->quantity) . ' Units';
                }

                $history[] = [
                    'action' => $actionType,
                    'action_label' => $actionLabel,
                    'badge_class' => $badgeClass,
                    'quantity' => $qtyDisplay,
                    'raw_qty' => $h->quantity,
                    'balance_after' => $h->balance_after . ' Units',
                    'reference' => $ref ?: 'Direct Inventory Adjustment',
                    'user_name' => $h->user_name ?: 'Staff',
                    'date' => Carbon::parse($h->created_at)->format('d M Y, h:i A'),
                    'relative_time' => Carbon::parse($h->created_at)->diffForHumans(),
                    'timestamp' => Carbon::parse($h->created_at)->timestamp,
                ];
            }

            // Also check ms_stock_audit_log for this part
            $auditLogs = DB::table('ms_stock_audit_log')
                ->leftJoin('users', 'ms_stock_audit_log.user_id', '=', 'users.id')
                ->select('ms_stock_audit_log.*', 'users.name as user_name')
                ->where('company_id', $companyId)
                ->where('item_type', 'part')
                ->where('item_id', $id)
                ->orderBy('ms_stock_audit_log.id', 'desc')
                ->get();

            foreach ($auditLogs as $al) {
                $already = false;
                foreach ($history as $existing) {
                    if ($existing['action'] === 'deleted' && abs($existing['raw_qty']) == abs($al->quantity) && $existing['date'] === Carbon::parse($al->created_at)->format('d M Y, h:i A')) {
                        $already = true;
                        break;
                    }
                }
                if (!$already) {
                    $history[] = [
                        'action' => $al->action,
                        'action_label' => $al->action === 'deletion' ? 'Stock Deleted / Reduced' : ucfirst($al->action),
                        'badge_class' => $al->action === 'deletion' ? 'badge-red' : 'badge-green',
                        'quantity' => '-' . abs($al->quantity) . ' Units',
                        'raw_qty' => -$al->quantity,
                        'balance_after' => ($al->balance_after !== null ? $al->balance_after . ' Units' : '—'),
                        'reference' => $al->reason ?: 'Manual Stock Deletion',
                        'user_name' => $al->user_name ?: 'Staff',
                        'date' => Carbon::parse($al->created_at)->format('d M Y, h:i A'),
                        'relative_time' => Carbon::parse($al->created_at)->diffForHumans(),
                        'timestamp' => Carbon::parse($al->created_at)->timestamp,
                    ];
                }
            }

            usort($history, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));

        } else {
            // Mobile Device: 'new_phone' or 'second_hand'
            $device = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->first();

            if (!$device) {
                return ['success' => false, 'message' => 'Mobile device not found', 'status_code' => 404];
            }

            $itemInfo = [
                'id' => $device->id,
                'name' => $device->brand . ' ' . $device->model . ($device->color ? ' (' . $device->color . ')' : ''),
                'type' => $device->type,
                'type_label' => $device->type === 'new' ? 'Brand New Smartphone' : 'Pre-Owned Device',
                'category' => $device->ram ? "{$device->ram}/{$device->storage}" : 'Standard Edition',
                'sku' => 'IMEI 1: ' . $device->imei_1 . ($device->imei_2 ? ' • IMEI 2: ' . $device->imei_2 : ''),
                'current_stock' => $device->status === 'in_stock' ? 'In Stock (1 unit)' : ($device->status === 'sold' ? 'Sold Out' : ucfirst($device->status)),
                'stock_qty' => $device->status === 'in_stock' ? 1 : 0,
                'status' => $device->status,
                'unit_cost' => '₹' . number_format($device->purchase_cost, 2),
                'selling_price' => '₹' . number_format($device->selling_price, 2),
                'is_in_stock' => $device->status === 'in_stock',
            ];

            // 1. ADDITION EVENT: When was it added and by who?
            $additionUser = 'Store Admin';
            $additionRef = 'Direct Stock Inward / Registration';

            if ($device->purchase_order_id) {
                $po = DB::table('ms_purchase_orders')
                    ->leftJoin('users', 'ms_purchase_orders.created_by', '=', 'users.id')
                    ->leftJoin('ms_suppliers', 'ms_purchase_orders.supplier_id', '=', 'ms_suppliers.id')
                    ->select('ms_purchase_orders.*', 'users.name as user_name', 'ms_suppliers.name as supplier_name')
                    ->where('ms_purchase_orders.id', $device->purchase_order_id)
                    ->first();
                if ($po) {
                    $additionUser = $po->user_name ?: 'Store Admin';
                    $additionRef = "Purchase Order #{$po->po_number}" . ($po->supplier_name ? " • Supplier: {$po->supplier_name}" : "");
                }
            } elseif (!empty($device->customer_buyback_name)) {
                $additionUser = 'Buyback Counter Staff';
                $additionRef = "Intake Buyback from {$device->customer_buyback_name}" . ($device->customer_buyback_phone ? " ({$device->customer_buyback_phone})" : "");
            }

            $history[] = [
                'action' => 'added',
                'action_label' => 'Stock Inward / Registered',
                'badge_class' => 'badge-green',
                'quantity' => '+1 Device Unit',
                'raw_qty' => 1,
                'balance_after' => '1 Unit in stock',
                'reference' => $additionRef,
                'user_name' => $additionUser,
                'date' => Carbon::parse($device->created_at)->format('d M Y, h:i A'),
                'relative_time' => Carbon::parse($device->created_at)->diffForHumans(),
                'timestamp' => Carbon::parse($device->created_at)->timestamp,
            ];

            // 2. SALE EVENT: Was it sold and by who?
            $sales = DB::table('ms_mobile_sales')
                ->leftJoin('users', 'ms_mobile_sales.sold_by', '=', 'users.id')
                ->leftJoin('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                ->select('ms_mobile_sales.*', 'users.name as user_name', 'ms_customers.name as customer_name')
                ->where('ms_mobile_sales.device_id', $id)
                ->orderBy('ms_mobile_sales.id', 'desc')
                ->get();

            foreach ($sales as $sale) {
                $history[] = [
                    'action' => 'sold',
                    'action_label' => 'Sold / Billed',
                    'badge_class' => 'badge-blue',
                    'quantity' => '-1 Device Unit',
                    'raw_qty' => -1,
                    'balance_after' => '0 Units (Sold Out)',
                    'reference' => "Tax Invoice #{$sale->invoice_number}" . ($sale->customer_name ? " • Customer: {$sale->customer_name}" : "") . " • Bill Amount: ₹" . number_format($sale->sale_price, 2) . " (" . strtoupper($sale->payment_mode) . ")",
                    'user_name' => $sale->user_name ?: 'Cashier Staff',
                    'date' => Carbon::parse($sale->created_at)->format('d M Y, h:i A'),
                    'relative_time' => Carbon::parse($sale->created_at)->diffForHumans(),
                    'timestamp' => Carbon::parse($sale->created_at)->timestamp,
                ];
            }

            // 3. DELETION / AUDIT LOGS: Was it deleted or written off?
            $auditLogs = DB::table('ms_stock_audit_log')
                ->leftJoin('users', 'ms_stock_audit_log.user_id', '=', 'users.id')
                ->select('ms_stock_audit_log.*', 'users.name as user_name')
                ->where('company_id', $companyId)
                ->where('item_id', $id)
                ->whereIn('item_type', ['new_phone', 'second_hand', 'phone', $device->type])
                ->orderBy('ms_stock_audit_log.id', 'desc')
                ->get();

            foreach ($auditLogs as $al) {
                $history[] = [
                    'action' => 'deleted',
                    'action_label' => 'Device Deleted / Removed',
                    'badge_class' => 'badge-red',
                    'quantity' => '-1 Device Unit',
                    'raw_qty' => -1,
                    'balance_after' => '0 Units (Removed)',
                    'reference' => $al->reason ?: 'Manual stock removal',
                    'user_name' => $al->user_name ?: 'Staff',
                    'date' => Carbon::parse($al->created_at)->format('d M Y, h:i A'),
                    'relative_time' => Carbon::parse($al->created_at)->diffForHumans(),
                    'timestamp' => Carbon::parse($al->created_at)->timestamp,
                ];
            }

            usort($history, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));
        }

        return [
            'success' => true,
            'item' => $itemInfo,
            'history' => $history,
        ];
    }
}
