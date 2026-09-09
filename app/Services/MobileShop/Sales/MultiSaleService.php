<?php

namespace App\Services\MobileShop\Sales;

use App\Services\MobileShop\Common\MobileShopInvoiceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MultiSaleService
{
    /**
     * Process multiple mobile devices sale with optional EMI, gift, and Khata udhari
     */
    public function store(Request $request, int $companyId): array
    {
        return DB::transaction(function () use ($request, $companyId) {
            // Find or create customer
            $customer = MobileShopInvoiceHelper::findOrCreateCustomer($companyId, $request);

            $billTotal = 0.00;
            $totalDeviceCost = 0.00;
            $devices = [];
            foreach ($request->device_ids as $idx => $devId) {
                $device = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('id', $devId)
                    ->where('status', 'in_stock')->where('type', 'new')
                    ->lockForUpdate()->first();
                if (!$device) {
                    return [
                        'success' => false,
                        'error' => "Device #{$devId} is no longer in stock. Sale cancelled — please retry.",
                    ];
                }
                $price = (float) ($request->sale_prices[$devId] ?? ($request->sale_prices[$idx] ?? $device->selling_price));
                if ($price < 1) $price = (float) $device->selling_price;
                $devices[] = ['device' => $device, 'price' => $price];
                $billTotal += $price;
                $totalDeviceCost += (float) $device->purchase_cost;
            }

            // Promotional Gift Resolution
            $hasGift = $request->boolean('has_gift');
            $giftName = null;
            $giftCost = 0.00;
            $giftPartId = null;

            if ($hasGift) {
                $giftSource = $request->input('gift_source', 'inventory');
                if ($giftSource === 'inventory' && $request->filled('gift_inventory_id')) {
                    $part = DB::table('ms_parts_inventory')
                        ->where('company_id', $companyId)
                        ->where('id', $request->gift_inventory_id)
                        ->lockForUpdate()
                        ->first();
                    if ($part) {
                        $giftPartId = $part->id;
                        $giftName = $part->name;
                        $giftCost = max(0.00, (float) ($request->filled('gift_cost') ? $request->gift_cost : $part->unit_cost));
                        if ($part->stock_qty >= 1) {
                            DB::table('ms_parts_inventory')->where('id', $part->id)->decrement('stock_qty', 1);
                            DB::table('ms_parts_inventory_history')->insert([
                                'part_id'     => $part->id,
                                'type'        => 'deduction',
                                'quantity'    => 1,
                                'notes'       => "Promotional free gift on phone sale for {$customer->name}",
                                'recorded_by' => auth()->id(),
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ]);
                        }
                    }
                } elseif ($giftSource === 'custom' && $request->filled('gift_custom_name')) {
                    $giftName = trim($request->gift_custom_name);
                    $giftCost = max(0.00, (float) $request->input('gift_cost', 0));
                    $giftPartId = null;
                }
            }

            // Payment Mode & EMI Financial Breakdown
            $paymentMode = $request->payment_mode === 'online' ? 'online' : $request->payment_mode;
            $isEmi = ($paymentMode === 'emi');
            $emiProviderId = $isEmi ? $request->emi_provider_id : null;
            $emiProcessingFee = 0.00;
            $emiFinanced = 0.00;
            $emiDownpaymentReq = 0.00;
            $udhariTotal = 0.00;

            if ($isEmi && $emiProviderId) {
                // Downpayment required by EMI scheme
                $emiDownpaymentReq = min($billTotal, max(0.00, (float) ($request->input('emi_downpayment_required') ?? $request->amount_paid)));
                // Amount financed by the EMI partner
                $emiFinanced = max(0.00, round($billTotal - $emiDownpaymentReq, 2));

                $feeType = $request->input('emi_fee_type', 'flat');
                $feeVal  = (float) ($request->input('emi_fee_value', $request->input('emi_processing_fee', 0)));
                $emiProcessingFee = $feeType === 'percent' ? round(($emiFinanced * $feeVal) / 100, 2) : round($feeVal, 2);

                // Downpayment customer actually pays right now
                $amountPaid = min(round((float) $request->amount_paid, 2), $billTotal);

                // If customer pays less than required downpayment, remaining short downpayment goes to Customer Khata (Udhari)
                $udhariTotal = max(0.00, round($emiDownpaymentReq - $amountPaid, 2));

                // Balance out with EMI company ledger
                $provider = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('id', $emiProviderId)->lockForUpdate()->first();
                if ($provider && $emiFinanced > 0) {
                    DB::table('ms_emi_providers')->where('id', $emiProviderId)->decrement('advance_balance', $emiFinanced);
                    DB::table('ms_emi_provider_transactions')->insert([
                        'emi_provider_id' => $emiProviderId,
                        'type'            => 'sale_deduction',
                        'amount'          => $emiFinanced,
                        'balance_after'   => $provider->advance_balance - $emiFinanced,
                        'reference_no'    => $request->emi_loan_no,
                        'notes'           => "EMI financing for {$customer->name} (Tenure: {$request->emi_tenure_months}m, DP Req: ₹" . number_format($emiDownpaymentReq, 2) . ($udhariTotal > 0 ? ", Customer short DP ₹" . number_format($udhariTotal, 2) . " moved to Khata" : "") . ")",
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            } else {
                $amountPaid = min(round((float) $request->amount_paid, 2), $billTotal);
                $udhariTotal = max(0.00, round($billTotal - $amountPaid, 2));
            }

            $storeState = MobileShopInvoiceHelper::getStoreStateCode();
            $reqTaxRate = (float) ($request->tax_rate ?? 18.00);
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst') || (!$request->has('bill_type') && !$request->has('is_gst'));
            $billType = $isGst ? 'gst' : 'non_gst';

            $firstInvoice = null;
            $lastInvoice = null;
            foreach ($devices as $d) {
                $invoiceNumber = MobileShopInvoiceHelper::getNextInvoiceNumber($companyId, 'INV');
                $salePrice = $d['price'];
                $gst = MobileShopInvoiceHelper::calculateGst($salePrice, $reqTaxRate, $billType, $storeState, $customer->state_code ?? null);

                // Proportional paid, udhari, gift cost, EMI fee across devices
                $share = $billTotal > 0 ? $salePrice / $billTotal : 0;
                $paidShare = round($amountPaid * $share, 2);
                $udhariShare = round($udhariTotal * $share, 2);
                $itemEmiFee = round($emiProcessingFee * $share, 2);
                $itemGiftCost = round($giftCost * $share, 2);
                $itemDownpayment = $isEmi ? round($emiDownpaymentReq * $share, 2) : 0.00;
                $itemFinanced = $isEmi ? round($emiFinanced * $share, 2) : 0.00;

                // True Net Profit
                $finalProfit = round($salePrice - (float) $d['device']->purchase_cost - $itemGiftCost - $itemEmiFee, 2);

                $saleId = DB::table('ms_mobile_sales')->insertGetId([
                    'company_id'          => $companyId,
                    'idempotency_key'     => Str::uuid()->toString(),
                    'customer_id'         => $customer->id,
                    'invoice_number'      => $invoiceNumber,
                    'bill_type'           => $gst['billType'],
                    'device_id'           => $d['device']->id,
                    'sale_price'          => $salePrice,
                    'gift_cost'           => $itemGiftCost,
                    'final_profit'        => $finalProfit,
                    'tax_rate'            => $gst['taxRate'],
                    'tax_type'            => $gst['taxType'],
                    'cgst_amount'         => $gst['cgst'],
                    'sgst_amount'         => $gst['sgst'],
                    'igst_amount'         => $gst['igst'],
                    'total_amount'        => $salePrice,
                    'amount_paid'         => $paidShare,
                    'udhari_amount'       => $udhariShare,
                    'payment_mode'        => $paymentMode,
                    'emi_provider_id'     => $emiProviderId,
                    'emi_loan_no'         => $request->emi_loan_no,
                    'emi_downpayment'     => $itemDownpayment,
                    'emi_financed_amount' => $itemFinanced,
                    'emi_processing_fee'  => $itemEmiFee,
                    'emi_tenure_months'   => $isEmi ? (int) ($request->emi_tenure_months ?: 12) : null,
                    'sold_by'             => auth()->id(),
                    'status'              => 'completed',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Attach gift item to sale
                if ($giftName) {
                    DB::table('ms_sale_gifts')->insert([
                        'sale_id'       => $saleId,
                        'gift_id'       => $giftPartId,
                        'gift_name'     => $giftName,
                        'purchase_cost' => $itemGiftCost,
                        'qty'           => 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                DB::table('ms_mobile_devices')->where('id', $d['device']->id)->update([
                    'status' => 'sold', 'selling_price' => $salePrice, 'updated_at' => now(),
                ]);

                if ($udhariShare > 0) {
                    DB::table('ms_customers')->where('id', $customer->id)->increment('udhari_balance', $udhariShare);
                    $newBal = (float) $customer->udhari_balance + $udhariShare;
                    $customer->udhari_balance = $newBal;
                    $udhariNote = $isEmi
                        ? "Short Downpayment (₹" . number_format($udhariShare, 2) . ") on EMI Sale #{$invoiceNumber}"
                        : "Udhari on Sale Invoice #{$invoiceNumber}";

                    DB::table('ms_customer_khata_transactions')->insert([
                        'company_id'   => $companyId,
                        'customer_id'  => $customer->id,
                        'type'         => 'udhari_sale',
                        'sale_id'      => $saleId,
                        'amount'       => $udhariShare,
                        'balance_after'=> $newBal,
                        'remarks'      => $udhariNote,
                        'recorded_by'  => auth()->id(),
                        'created_at'   => now(),
                    ]);
                }

                $firstInvoice = $firstInvoice ?: $saleId;
                $lastInvoice = $saleId;
            }

            return [
                'success'       => true,
                'first_invoice' => $firstInvoice,
                'count'         => count($devices),
                'customer_name' => $customer->name,
                'bill_total'    => $billTotal,
                'is_emi'        => $isEmi,
                'emi_financed'  => $emiFinanced,
                'amount_paid'   => $amountPaid,
                'udhari_total'  => $udhariTotal,
                'gift_name'     => $giftName,
            ];
        });
    }
}
