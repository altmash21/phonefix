<?php

namespace App\Services\MobileShop\Stock;

use App\Repositories\MobileShop\Contracts\MsStockRepositoryInterface;
use App\Repositories\MobileShop\MsStockRepository;
use Illuminate\Support\Facades\DB;

class StockDeletionService
{
    protected MsStockRepositoryInterface $stockRepo;

    public function __construct(?MsStockRepositoryInterface $stockRepo = null)
    {
        $this->stockRepo = $stockRepo ?? app(MsStockRepositoryInterface::class);
    }

    /**
     * Delete / reduce stock item (part or mobile phone) and record audit trail
     */
    public function deleteItem(string $type, int $id, ?int $quantity, string $fullReason, int $companyId, object $user): array
    {
        if ($type === 'part') {
            $part = DB::table('ms_parts_inventory')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->first();

            if (!$part) {
                return ['success' => false, 'message' => 'Part item not found in stock.', 'status_code' => 404];
            }

            $currentStock = (int) $part->stock_qty;
            $qtyToDelete  = (int) ($quantity ?? 1);

            if ($currentStock > 0 && $qtyToDelete > $currentStock) {
                return [
                    'success' => false,
                    'message' => "Cannot delete {$qtyToDelete} units. Only {$currentStock} units available in stock.",
                    'status_code' => 422,
                ];
            }

            $newStock = max(0, $currentStock - $qtyToDelete);

            DB::transaction(function () use ($id, $newStock, $qtyToDelete, $fullReason, $user, $companyId) {
                DB::table('ms_parts_inventory')->where('id', $id)->update([
                    'stock_qty' => $newStock,
                    'updated_at' => now(),
                ]);

                DB::table('ms_parts_inventory_history')->insert([
                    'part_id'       => $id,
                    'type'          => 'deduction',
                    'quantity'      => -$qtyToDelete,
                    'balance_after' => $newStock,
                    'reference'     => "Stock Deletion / Write-off: {$fullReason}",
                    'user_id'       => $user->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                DB::table('ms_stock_audit_log')->insert([
                    'company_id'    => $companyId,
                    'item_type'     => 'part',
                    'item_id'       => $id,
                    'action'        => 'deletion',
                    'quantity'      => $qtyToDelete,
                    'balance_after' => $newStock,
                    'reason'        => $fullReason,
                    'user_id'       => $user->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            });

            return [
                'success' => true,
                'message' => "Successfully removed {$qtyToDelete} unit(s) of '{$part->name}'. Remaining stock: {$newStock} units. Action logged by {$user->name}.",
                'new_stock' => $newStock,
                'item_id' => $id,
                'item_type' => 'part',
            ];

        } else {
            // Phone: new_phone or second_hand
            $device = $this->stockRepo->findById($id, $companyId);

            if (!$device) {
                return ['success' => false, 'message' => 'Mobile device not found in inventory.', 'status_code' => 404];
            }

            if ($device->status === 'sold') {
                return [
                    'success' => false,
                    'message' => 'Cannot delete a device that has already been sold. Please process a Sales Return if needed.',
                    'status_code' => 422,
                ];
            }

            DB::transaction(function () use ($id, $device, $type, $fullReason, $user, $companyId) {
                $this->stockRepo->updateStatus($id, 'deleted');

                DB::table('ms_stock_audit_log')->insert([
                    'company_id'    => $companyId,
                    'item_type'     => $type,
                    'item_id'       => $id,
                    'action'        => 'deletion',
                    'quantity'      => 1,
                    'balance_after' => 0,
                    'reason'        => $fullReason,
                    'user_id'       => $user->id,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            });

            $devName = "{$device->brand} {$device->model} (IMEI: {$device->imei_1})";
            return [
                'success' => true,
                'message' => "Device '{$devName}' removed from stock. Action logged by {$user->name}.",
                'status'  => 'deleted',
                'item_id' => $id,
                'item_type' => $type,
            ];
        }
    }
}
