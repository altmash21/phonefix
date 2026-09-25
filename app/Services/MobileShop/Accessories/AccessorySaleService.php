<?php

namespace App\Services\MobileShop\Accessories;

use App\Services\MobileShop\Common\MobileShopInvoiceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccessorySaleService
{
    /**
     * Sell Accessories at Retail Counter POS (Multi-Item Cart Support & Atomic Locking)
     */
    public function sellAccessory(int $companyId, Request $request, string $storeState): array
    {
        $request->validate([
            'customer_phone'     => 'required',
            'customer_name'      => 'required',
            'items'              => 'required|array|min:1',
            'items.*.part_id'    => 'required|exists:ms_parts_inventory,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'amount_paid'        => 'required|numeric|min:0',
            'payment_mode'       => 'required|in:cash,upi,card,credit_udhari,split,udhari,cash+upi,cash+udhari,upi+udhari',
        ]);

        // Idempotency check
        if ($request->filled('idempotency_key')) {
            $existing = DB::table('ms_accessory_sales')
                ->where('company_id', $companyId)
                ->where('idempotency_key', $request->idempotency_key)
                ->first();
            if ($existing) {
                return [
                    'is_duplicate'   => true,
                    'sale_id'        => $existing->id,
                    'invoice_number' => $existing->invoice_number,
                ];
            }
        }

        return DB::transaction(function () use ($request, $companyId, $storeState) {
            $customer = MobileShopInvoiceHelper::findOrCreateCustomer($companyId, $request);

            // Atomic Sequential Invoice Number
            $invoiceNumber = MobileShopInvoiceHelper::getNextInvoiceNumber($companyId, 'ACC');

            $subtotal = 0.00;
            $grossTotal = 0.00;
            $lineItemsData = [];

            // Lock and Validate Every Item in Cart
            foreach ($request->items as $cartItem) {
                $partId = $cartItem['part_id'];
                $qty = (int) $cartItem['quantity'];
                $price = (float) $cartItem['unit_price'];

                $part = DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->where('id', $partId)
                    ->lockForUpdate()
                    ->first();

                if (!$part) {
                    abort(422, "Accessory item ID #{$partId} not found in inventory!");
                }

                if ($part->stock_qty < $qty) {
                    abort(422, "Insufficient stock for '{$part->name}'! Available: {$part->stock_qty}, Requested: {$qty}");
                }

                // Decrement stock
                $newBalance = $part->stock_qty - $qty;
                DB::table('ms_parts_inventory')
                    ->where('id', $part->id)
                    ->update([
                        'stock_qty'  => $newBalance,
                        'updated_at' => now(),
                    ]);

                // Log deduction in stock history ledger
                DB::table('ms_parts_inventory_history')->insert([
                    'part_id'       => $part->id,
                    'type'          => 'deduction',
                    'quantity'      => $qty,
                    'balance_after' => $newBalance,
                    'reference'     => "Retail Counter Sale #{$invoiceNumber}",
                    'user_id'       => auth()->id(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                $lineTotal = round($price * $qty, 2);
                $origUnitPrice = (float) ($part->selling_price ?: $price);
                $origLineTotal = round($origUnitPrice * $qty, 2);
                $lineDiscount = max(0.00, round($origLineTotal - $lineTotal, 2));

                $subtotal += $lineTotal;
                $grossTotal += $origLineTotal;

                $lineItemsData[] = [
                    'company_id'      => $companyId,
                    'part_id'         => $part->id,
                    'part_name'       => $part->name,
                    'hsn_code'        => $part->hsn_code ?? '85177090',
                    'quantity'        => $qty,
                    'unit_cost'       => $part->unit_cost,
                    'unit_price'      => $price,
                    'original_price'  => $origUnitPrice,
                    'discount_amount' => $lineDiscount,
                    'tax_rate'        => 18.00,
                    'tax_amount'      => round($lineTotal - ($lineTotal / 1.18), 2),
                    'line_total'      => $lineTotal,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            $invoiceDiscount = max(0.00, round($grossTotal - $subtotal, 2));
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst');
            $billType = $isGst ? 'gst' : 'non_gst';

            $gstCalc = MobileShopInvoiceHelper::calculateGst($subtotal, 18.00, $billType, $storeState, $customer->state_code ?? null);

            $amountPaid = (float) $request->amount_paid;
            $udhariAmount = max(0.00, $subtotal - $amountPaid);

            // Insert Header Record
            $saleId = DB::table('ms_accessory_sales')->insertGetId([
                'company_id'      => $companyId,
                'idempotency_key' => $request->idempotency_key ?? Str::uuid()->toString(),
                'invoice_number'  => $invoiceNumber,
                'bill_type'       => $billType,
                'customer_id'     => $customer->id,
                'gross_total'     => $grossTotal,
                'discount_amount' => $invoiceDiscount,
                'subtotal'        => $subtotal,
                'tax_rate'        => $gstCalc['taxRate'],
                'tax_amount'      => $gstCalc['totalTax'],
                'cgst_amount'     => $gstCalc['cgst'],
                'sgst_amount'     => $gstCalc['sgst'],
                'igst_amount'     => $gstCalc['igst'],
                'total_amount'    => $subtotal,
                'amount_paid'     => $amountPaid,
                'udhari_amount'   => $udhariAmount,
                'payment_mode'    => $request->payment_mode,
                'sold_by'         => auth()->id(),
                'status'          => 'completed',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Insert Line Items
            foreach ($lineItemsData as &$line) {
                $line['accessory_sale_id'] = $saleId;
            }
            DB::table('ms_accessory_sale_items')->insert($lineItemsData);

            // Update Customer Khata if Udhari
            if ($udhariAmount > 0) {
                DB::table('ms_customers')->where('id', $customer->id)->increment('udhari_balance', $udhariAmount);
                $newBal = $customer->udhari_balance + $udhariAmount;
                DB::table('ms_customer_khata_transactions')->insert([
                    'company_id'    => $companyId,
                    'customer_id'   => $customer->id,
                    'type'          => 'udhari_sale',
                    'sale_id'       => null,
                    'amount'        => $udhariAmount,
                    'balance_after' => $newBal,
                    'remarks'       => "Udhari on Accessory Sale #{$invoiceNumber}",
                    'recorded_by'   => auth()->id(),
                    'created_at'    => now(),
                ]);
            }

            return [
                'is_duplicate'   => false,
                'sale_id'        => $saleId,
                'invoice_number' => $invoiceNumber,
            ];
        });
    }
}
