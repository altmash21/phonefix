<?php

namespace App\Services\MobileShop\Sales;

use Illuminate\Support\Facades\DB;

class SaleVoidService
{
    /**
     * Void a mobile phone sale, restore stock and gifts, and reverse customer khata transactions
     */
    public function void(int $id, int $companyId, bool $shouldRestock, string $auditReason, ?int $userId = null): array
    {
        $userId = $userId ?: auth()->id();

        return DB::transaction(function () use ($id, $companyId, $shouldRestock, $auditReason, $userId) {
            $sale = DB::table('ms_mobile_sales')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$sale || $sale->status === 'voided') {
                return [
                    'success' => false,
                    'error' => 'Sale is already voided or cannot be found.',
                ];
            }

            // Update Device status based on condition
            $newDeviceStatus = $shouldRestock ? 'in_stock' : 'returned';
            DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('id', $sale->device_id)->update([
                'status' => $newDeviceStatus,
                'updated_at' => now(),
            ]);

            // Restore gifts if any to accessories & parts inventory
            $gifts = DB::table('ms_sale_gifts')->where('sale_id', $id)->get();
            foreach ($gifts as $g) {
                $part = DB::table('ms_parts_inventory')->where('id', $g->gift_id)->lockForUpdate()->first();
                if ($part) {
                    $newBalance = $part->stock_qty + ($shouldRestock ? $g->qty : 0);
                    if ($shouldRestock) {
                        DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                            'stock_qty' => $newBalance,
                            'updated_at' => now(),
                        ]);
                    }
                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id' => $part->id,
                        'type' => $shouldRestock ? 'addition' : 'deduction',
                        'quantity' => $g->qty,
                        'balance_after' => $shouldRestock ? $newBalance : $part->stock_qty,
                        'reference' => "Restored Gift from Returned Mobile Sale #{$sale->invoice_number}" . ($shouldRestock ? "" : " (Marked Defective)"),
                        'user_id' => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Reverse Customer Khata Balance if Udhari was recorded
            if ($sale->udhari_amount > 0 && $sale->customer_id) {
                $customer = DB::table('ms_customers')->where('id', $sale->customer_id)->lockForUpdate()->first();
                if ($customer) {
                    $newBal = max(0.00, $customer->udhari_balance - $sale->udhari_amount);
                    DB::table('ms_customers')->where('id', $customer->id)->update([
                        'udhari_balance' => $newBal,
                        'updated_at' => now(),
                    ]);

                    DB::table('ms_customer_khata_transactions')->insert([
                        'company_id' => $companyId,
                        'customer_id' => $customer->id,
                        'type' => 'adjustment',
                        'amount' => $sale->udhari_amount,
                        'balance_after' => $newBal,
                        'remarks' => "Reversal for Returned Mobile Sale #{$sale->invoice_number}",
                        'recorded_by' => $userId,
                        'created_at' => now(),
                    ]);
                }
            }

            // Mark Sale as Voided
            DB::table('ms_mobile_sales')->where('id', $id)->update([
                'status' => 'voided',
                'voided_by' => $userId,
                'voided_at' => now(),
                'void_reason' => $auditReason . ($shouldRestock ? " [Restocked to Inventory]" : " [Marked Defective]"),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'invoice_number' => $sale->invoice_number,
            ];
        });
    }
}
