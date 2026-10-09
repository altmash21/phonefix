<?php

namespace App\Services\MobileShop\Accessories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccessoryVoidService
{
    /**
     * Process sales return / void for accessory sale
     */
    public function voidAccessorySale(int $companyId, int $id, Request $request, bool $isOwner, callable $verifyOtpCallback): array
    {
        $request->validate([
            'void_reason'        => 'nullable|string',
            'return_reason_code' => 'nullable|string',
        ]);

        $shouldRestock       = $request->has('should_restock') ? (bool) $request->should_restock : true;
        $reasonLabel         = $request->input('reason_label') ?: ($request->input('return_reason_code') ?: 'Customer Return');
        $customDetails       = $request->input('void_reason') ? " — {$request->input('void_reason')}" : "";
        $auditReason         = "{$reasonLabel}{$customDetails}";
        $returnedItemsInput  = $request->input('returned_items', []);

        // OTP Security Gate: Only owner can void without OTP
        $itemRef = "acc_sale:{$id}";
        if (!$isOwner) {
            $otpCode = $request->input('otp_code');
            if (!$verifyOtpCallback($companyId, 'void_accessory_sale', $itemRef, $otpCode)) {
                return [
                    'success'        => false,
                    'otp_required'   => true,
                    'action'         => 'void_accessory_sale',
                    'item_reference' => $itemRef,
                    'message'        => 'Store Owner OTP authorization is required to void sales invoices.',
                ];
            }
        }

        return DB::transaction(function () use ($id, $companyId, $shouldRestock, $auditReason, $returnedItemsInput) {
            $sale = DB::table('ms_accessory_sales')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$sale || $sale->status === 'voided') {
                return [
                    'success' => false,
                    'message' => 'Sale is already voided or cannot be found.',
                ];
            }

            $items = DB::table('ms_accessory_sale_items')
                ->where('company_id', $companyId)
                ->where('accessory_sale_id', $id)
                ->get();

            $totalRefundCalculated   = 0.00;
            $itemsProcessedCount     = 0;
            $totalOriginalItemsCount = $items->count();
            $returnedItemRecords     = [];

            foreach ($items as $item) {
                // Determine if this item is selected for return
                $returnQty = 0;
                if (!empty($returnedItemsInput)) {
                    if (isset($returnedItemsInput[$item->id]) && (int) $returnedItemsInput[$item->id] > 0) {
                        $returnQty = min((int) $returnedItemsInput[$item->id], $item->quantity);
                    }
                } else {
                    // Full return fallback
                    $returnQty = $item->quantity;
                }

                if ($returnQty <= 0) {
                    continue;
                }

                $itemsProcessedCount++;
                $unitPrice = $item->unit_price > 0 ? (float) $item->unit_price : ((float) $item->line_total / max(1, $item->quantity));
                $itemRefund = round($unitPrice * $returnQty, 2);
                $totalRefundCalculated += $itemRefund;

                $returnedItemRecords[] = [
                    'item_id'     => $item->id,
                    'part_id'     => $item->part_id,
                    'part_name'   => $item->part_name,
                    'quantity'    => $returnQty,
                    'unit_price'  => $unitPrice,
                    'line_total'  => $itemRefund,
                    'reason'      => $auditReason,
                    'restocked'   => $shouldRestock,
                    'returned_at' => now()->toIso8601String(),
                ];

                // 1. Restore Inventory Stock
                $part = null;
                if (!empty($item->part_id)) {
                    $part = DB::table('ms_parts_inventory')->where('id', $item->part_id)->lockForUpdate()->first();
                }
                if (!$part && !empty($item->part_name)) {
                    $part = DB::table('ms_parts_inventory')
                        ->where('company_id', $companyId)
                        ->where('name', $item->part_name)
                        ->lockForUpdate()
                        ->first();
                }

                if ($part) {
                    if ($shouldRestock) {
                        $restoredStock = $part->stock_qty + $returnQty;
                        DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                            'stock_qty'  => $restoredStock,
                            'updated_at' => now(),
                        ]);

                        // Log stock restoration
                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id'       => $part->id,
                            'type'          => 'addition',
                            'quantity'      => $returnQty,
                            'balance_after' => $restoredStock,
                            'reference'     => "RESTOCKED: {$returnQty}x {$item->part_name} from Sale #{$sale->invoice_number} ({$auditReason}) by " . (auth()->user()?->name ?? 'Staff'),
                            'user_id'       => auth()->id(),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    } else {
                        // Log as Defective / Quarantined without increasing sellable stock
                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id'       => $part->id,
                            'type'          => 'deduction',
                            'quantity'      => $returnQty,
                            'balance_after' => $part->stock_qty,
                            'reference'     => "DEFECTIVE RETURN (Quarantined/Scrap): {$returnQty}x {$item->part_name} from Sale #{$sale->invoice_number} ({$auditReason}) by " . (auth()->user()?->name ?? 'System'),
                            'user_id'       => auth()->id(),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);

                        // Record in Defective Items registry
                        DB::table('ms_defective_items')->insert([
                            'company_id'    => $companyId,
                            'part_id'       => $part->id,
                            'item_name'     => $item->part_name,
                            'source_type'   => 'sale_return',
                            'source_id'     => $sale->id,
                            'source_ref'    => $sale->invoice_number,
                            'supplier_id'   => null,
                            'supplier_name' => null,
                            'customer_name' => $sale->customer_name ?? null,
                            'qty'           => $returnQty,
                            'unit_cost'     => (float) ($part->unit_cost ?? 0),
                            'total_cost'    => round(((float) ($part->unit_cost ?? 0)) * $returnQty, 2),
                            'defect_reason' => $auditReason,
                            'status'        => 'pending_supplier_return',
                            'notes'         => "Returned by customer on sale return #{$sale->invoice_number}",
                            'created_by'    => auth()->id(),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                } elseif ($shouldRestock && !empty($item->part_name)) {
                    // Create inventory item if none existed so stock is preserved
                    $newPartId = DB::table('ms_parts_inventory')->insertGetId([
                        'company_id'       => $companyId,
                        'name'             => $item->part_name,
                        'category'         => 'general_accessory',
                        'brand'            => 'Universal',
                        'compatible_model' => 'Universal',
                        'display_type'     => 'na',
                        'hsn_code'         => $item->hsn_code ?? '85177090',
                        'unit_cost'        => (float) ($item->unit_cost ?? 0),
                        'selling_price'    => (float) ($item->unit_price ?? 0),
                        'stock_qty'        => $returnQty,
                        'min_stock_alert'  => 3,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);

                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id'       => $newPartId,
                        'type'          => 'addition',
                        'quantity'      => $returnQty,
                        'balance_after' => $returnQty,
                        'reference'     => "RESTOCKED (New Item): {$returnQty}x {$item->part_name} from Sale #{$sale->invoice_number} ({$auditReason}) by " . (auth()->user()?->name ?? 'Staff'),
                        'user_id'       => auth()->id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                // 2. Update line-item returned quantity if column exists
                $prevReturned = (int) ($item->returned_qty ?? 0);
                $newReturnedQty = min($item->quantity, $prevReturned + $returnQty);
                $newReturnedAmount = round($unitPrice * $newReturnedQty, 2);

                $itemUpdate = [];
                if (Schema::hasColumn('ms_accessory_sale_items', 'returned_qty')) {
                    $itemUpdate['returned_qty'] = $newReturnedQty;
                }
                if (Schema::hasColumn('ms_accessory_sale_items', 'returned_amount')) {
                    $itemUpdate['returned_amount'] = $newReturnedAmount;
                }
                if (!empty($itemUpdate)) {
                    $itemUpdate['updated_at'] = now();
                    DB::table('ms_accessory_sale_items')->where('id', $item->id)->update($itemUpdate);
                }
            }

            if ($itemsProcessedCount === 0) {
                return [
                    'success' => false,
                    'message' => 'Please select at least one item to return.',
                ];
            }

            // Reverse Customer Khata Balance if Udhari was recorded
            if ($sale->udhari_amount > 0 && $sale->customer_id) {
                $customer = DB::table('ms_customers')->where('id', $sale->customer_id)->lockForUpdate()->first();
                if ($customer) {
                    $khataReversal = min($totalRefundCalculated, (float) $sale->udhari_amount);
                    $newBal = max(0.00, $customer->udhari_balance - $khataReversal);
                    DB::table('ms_customers')->where('id', $customer->id)->update([
                        'udhari_balance' => $newBal,
                        'updated_at'     => now(),
                    ]);

                    DB::table('ms_customer_khata_transactions')->insert([
                        'company_id'    => $companyId,
                        'customer_id'   => $customer->id,
                        'type'          => 'adjustment',
                        'amount'        => $khataReversal,
                        'balance_after' => $newBal,
                        'remarks'       => "Reversal for Returned Items on Sale #{$sale->invoice_number}",
                        'recorded_by'   => auth()->id(),
                        'created_at'    => now(),
                    ]);
                }
            }

            // Calculate cumulative refund and net amount
            $prevRefund = (float) ($sale->refund_amount ?? 0);
            $cumulativeRefund = round($prevRefund + $totalRefundCalculated, 2);
            $netAmount = max(0.00, round((float) $sale->total_amount - $cumulativeRefund, 2));

            // Mark Header status (voided if all items returned, or partially_returned)
            $isAllReturned = ($cumulativeRefund >= (float) $sale->total_amount || $itemsProcessedCount >= $totalOriginalItemsCount);
            $newStatus = $isAllReturned ? 'voided' : 'partially_returned';

            // Decode and append existing return details
            $existingReturnDetails = [];
            if (!empty($sale->return_details)) {
                $decoded = is_string($sale->return_details) ? json_decode($sale->return_details, true) : $sale->return_details;
                if (is_array($decoded)) {
                    $existingReturnDetails = $decoded;
                }
            }
            $existingReturnDetails[] = [
                'batch_id'      => 'RET-' . date('YmdHis'),
                'date'          => now()->format('Y-m-d H:i:s'),
                'reason'        => $auditReason,
                'restocked'     => $shouldRestock,
                'refund_amount' => $totalRefundCalculated,
                'processed_by'  => auth()->user()?->name ?? 'Staff',
                'items'         => $returnedItemRecords,
            ];

            $saleUpdate = [
                'status'      => $newStatus,
                'voided_by'   => auth()->id(),
                'voided_at'   => now(),
                'void_reason' => $auditReason . ($shouldRestock ? " [Restocked to Inventory]" : " [Quarantined/Defective]"),
                'updated_at'  => now(),
            ];

            if (Schema::hasColumn('ms_accessory_sales', 'refund_amount')) {
                $saleUpdate['refund_amount'] = $cumulativeRefund;
            }
            if (Schema::hasColumn('ms_accessory_sales', 'net_amount')) {
                $saleUpdate['net_amount'] = $netAmount;
            }
            if (Schema::hasColumn('ms_accessory_sales', 'return_details')) {
                $saleUpdate['return_details'] = json_encode($existingReturnDetails);
            }

            // Adjust amount_paid if cash/upi refund was given (and no udhari)
            if ($sale->udhari_amount <= 0 && $sale->amount_paid > 0) {
                $saleUpdate['amount_paid'] = max(0.00, round((float) $sale->amount_paid - $totalRefundCalculated, 2));
            } elseif ($sale->udhari_amount > 0) {
                $saleUpdate['udhari_amount'] = max(0.00, round((float) $sale->udhari_amount - min($totalRefundCalculated, (float) $sale->udhari_amount), 2));
            }

            DB::table('ms_accessory_sales')->where('id', $id)->update($saleUpdate);

            $restockMsg = $shouldRestock ? 'and restocked to inventory' : 'and quarantined (defective)';
            return [
                'success' => true,
                'message' => "Return processed successfully! Refund Amount: ₹" . number_format($totalRefundCalculated, 2) . " ({$itemsProcessedCount} item(s) returned {$restockMsg}).",
            ];
        });
    }
}

