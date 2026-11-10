<?php

namespace App\Services\MobileShop\Common;

use Illuminate\Support\Facades\DB;

class MobileShopInvoiceResolver
{
    /**
     * Helper to Convert Indian Rupees Amount to Words
     */
    public static function amountToWords($number): string
    {
        $number = abs((float) $number);
        $no = (int) floor($number);
        $decimal = (int) round(($number - $no) * 100);

        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

        $str = [];
        $n_length = strlen((string) $no);
        $i = 0;
        while ($i < $n_length) {
            $divider = ($i == 2) ? 10 : 100;
            $unit = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($unit) {
                $plural = (($counter = count($str)) && $unit > 9) ? 's' : null;
                $hundred = ($counter == 1 && !empty($str[0])) ? ' and ' : null;
                $str[] = ($unit < 21)
                    ? ($words[$unit] ?? '') . ' ' . ($digits[$counter] ?? '') . $plural . ' ' . $hundred
                    : (($words[(int)floor($unit / 10) * 10] ?? '') . ' ' . ($words[$unit % 10] ?? '')) . ' ' . ($digits[$counter] ?? '') . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        $rupees = trim(implode('', array_reverse(array_filter($str))));

        $paise = '';
        if ($decimal > 0) {
            $paiseStr = ($decimal < 21)
                ? ($words[$decimal] ?? '')
                : (($words[(int)floor($decimal / 10) * 10] ?? '') . ' ' . ($words[$decimal % 10] ?? ''));
            $paise = " and " . trim($paiseStr) . ' Paise';
        }

        return 'Rupees ' . ($rupees !== '' ? $rupees : 'Zero') . $paise . ' Only';
    }

    /**
     * Normalize payment mode for customer statement entries
     */
    public static function normalizePaymentMode(?string $mode, ?string $text = ''): ?string
    {
        $m = strtolower(trim((string) $mode));
        $t = strtolower(trim((string) $text));

        // Return reversals or adjustment credits are not cash/online receipts
        if ($m === 'reversal' || str_contains($t, 'reversal') || str_contains($t, 'returned items') || str_contains($t, 'returned:')) {
            return 'reversal';
        }

        if (in_array($m, ['cash', 'upi', 'online', 'bank_transfer', 'cheque', 'card', 'split', 'cash+upi'])) {
            if (in_array($m, ['upi', 'online'])) {
                return 'upi';
            }
            if ($m === 'cash+upi') {
                return 'split';
            }
            return $m;
        }

        // Fallback checks from description/remarks if payment_mode is missing or unmapped
        if (str_contains($t, 'cash portion') || str_contains($t, '(cash)') || str_contains($t, 'paid cash')) {
            return 'cash';
        }
        if (str_contains($t, 'upi portion') || str_contains($t, '(upi)') || str_contains($t, 'gpay') || str_contains($t, 'phonepe') || str_contains($t, 'paytm') || str_contains($t, 'online')) {
            return 'upi';
        }
        if (str_contains($t, 'bank transfer') || str_contains($t, 'neft') || str_contains($t, 'rtgs') || str_contains($t, 'imps') || str_contains($t, 'bank')) {
            return 'bank_transfer';
        }
        if (str_contains($t, 'cheque')) {
            return 'cheque';
        }

        return !empty($m) ? $m : 'cash';
    }

    /**
     * Format payment mode label for display
     */
    public static function formatPaymentModeLabel(?string $mode): string
    {
        switch (strtolower(trim((string) $mode))) {
            case 'cash':
                return 'Cash';
            case 'upi':
            case 'online':
                return 'Online (UPI)';
            case 'bank_transfer':
                return 'Bank Transfer';
            case 'cheque':
                return 'Cheque';
            case 'card':
                return 'Card';
            case 'split':
                return 'Cash + Online';
            case 'reversal':
                return 'Return Reversal';
            default:
                return !empty($mode) ? ucfirst(str_replace('_', ' ', $mode)) : 'Cash';
        }
    }

    /**
     * Build Tally-Style Customer Ledger Statement (Chronological with running balance)
     */
    public static function buildCustomerLedgerStatement(int $companyId, int $customerId, array $filters = []): array
    {
        $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('id', $customerId)->first();
        if (!$customer) {
            return [
                'customer'            => null,
                'ledger'              => collect([]),
                'fullLedgerCount'     => 0,
                'filteredLedgerCount' => 0,
                'currentFilter'       => 'active',
                'periodLabel'         => 'Statement',
                'fromDate'            => null,
                'toDate'              => null,
                'openingBalance'      => 0.00,
                'hasPriorHistory'     => false,
                'priorSettledCount'   => 0,
                'totalBilled'         => 0.00,
                'totalPaid'           => 0.00,
                'totalPaidCash'       => 0.00,
                'totalPaidOnline'     => 0.00,
                'totalPaidBank'       => 0.00,
                'totalPaidCheque'     => 0.00,
                'totalReturnCredits'  => 0.00,
                'closingBalance'      => 0.00,
            ];
        }

        $prefix = DB::getTablePrefix();

        // 1. Khata Repayments, Old Udhar / Opening Balances & Adjustments (Loaded first for reversal cross-referencing)
        $khataTxs = DB::table('ms_customer_khata_transactions')
            ->where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('type', '!=', 'udhari_sale')
            ->get()
            ->map(function ($tx) use ($companyId) {
                $isDebit = in_array($tx->type, ['opening_balance', 'old_udhar', 'debit_adjustment']);
                $rawRemarks = trim((string) ($tx->remarks ?? ''));
                $hasCustomDesc = !empty($rawRemarks) && !in_array(strtolower($rawRemarks), ['old udhar / opening balance', 'old udhar', 'opening balance']);
                
                $detailsDesc = $rawRemarks;

                if ($isDebit) {
                    $itemDesc = $hasCustomDesc ? "Old Udhar: {$rawRemarks}" : 'Old Udhar / Opening Balance';
                    $entryPaymentMode = null;
                } else {
                    $isReversal = ($tx->type === 'adjustment' || stripos($rawRemarks, 'reversal') !== false || stripos($rawRemarks, 'returned') !== false);
                    if ($isReversal && preg_match('/#([A-Za-z0-9\-_]+)/', $rawRemarks, $m)) {
                        $invoiceRef = $m[1];
                        $returnedSummary = self::resolveReturnedItemSummary($companyId, $invoiceRef, (float) $tx->amount);
                        if ($returnedSummary && !str_contains($rawRemarks, $returnedSummary)) {
                            $itemDesc = "Reversal for Returned: {$returnedSummary}";
                            $detailsDesc = "Sale #{$invoiceRef}";
                        } else {
                            $itemDesc = !empty($rawRemarks) ? $rawRemarks : 'Return Adjustment';
                        }
                        $entryPaymentMode = 'reversal';
                    } elseif ($isReversal) {
                        $itemDesc = !empty($rawRemarks) ? $rawRemarks : 'Return Adjustment';
                        $entryPaymentMode = 'reversal';
                    } else {
                        $itemDesc = !empty($rawRemarks) ? $rawRemarks : 'Khata repayment received';
                        $entryPaymentMode = self::normalizePaymentMode($tx->payment_mode ?? null, $rawRemarks . ' ' . ($tx->reference_no ?? ''));
                    }
                }

                return (object) [
                    'id'            => $tx->id,
                    'created_at'    => $tx->created_at,
                    'sort_order'    => 500,
                    'ref_no'        => $tx->reference_no ?: ($isDebit ? 'OLD-UDHAR' : ('KHATA-' . $tx->id)),
                    'particulars'   => $itemDesc,
                    'item_name'     => $itemDesc,
                    'description'   => $detailsDesc,
                    'billed_amount' => $isDebit ? (float) $tx->amount : 0.00,
                    'paid_amount'   => $isDebit ? 0.00 : (float) $tx->amount,
                    'due_amount'    => $isDebit ? (float) $tx->amount : 0.00,
                    'entry_type'    => $tx->type,
                    'source'        => 'khata_transaction',
                    'source_id'     => (int) $tx->id,
                    'payment_mode'  => $entryPaymentMode,
                    'raw_remarks'   => $tx->remarks ?? '',
                ];
            });

        // Helper to check existing return/reversal credits in Khata for an invoice
        $getExistingReversals = function (string $invoiceRef) use ($khataTxs): float {
            $ref = trim($invoiceRef);
            if (empty($ref)) return 0.00;
            return (float) $khataTxs->filter(function ($tx) use ($ref) {
                $raw = ($tx->raw_remarks ?? '') . ' ' . ($tx->description ?? '') . ' ' . ($tx->ref_no ?? '') . ' ' . ($tx->particulars ?? '');
                return str_contains($raw, $ref) && (
                    $tx->entry_type === 'adjustment' ||
                    $tx->entry_type === 'refund_credit' ||
                    $tx->payment_mode === 'reversal' ||
                    stripos($raw, 'reversal') !== false ||
                    stripos($raw, 'returned') !== false
                );
            })->sum('paid_amount');
        };

        // 2. Mobile Sales (Include active udhari sales or returned/voided sales that had udhari recorded)
        $mobileSales = DB::table('ms_mobile_sales')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.customer_id', $customerId)
            ->where(function ($q) {
                $q->where(function ($activeQ) {
                    $activeQ->whereNotIn('ms_mobile_sales.status', ['voided', 'returned'])
                        ->where(function ($w) {
                            $w->where('ms_mobile_sales.udhari_amount', '>', 0)
                              ->orWhereColumn('ms_mobile_sales.amount_paid', '<', 'ms_mobile_sales.total_amount');
                        });
                })->orWhere(function ($retQ) {
                    $retQ->whereIn('ms_mobile_sales.status', ['voided', 'returned'])
                        ->where(function ($w) {
                            $w->where('ms_mobile_sales.udhari_amount', '>', 0)
                              ->orWhere('ms_mobile_sales.payment_mode', 'LIKE', '%udhari%')
                              ->orWhere('ms_mobile_sales.payment_mode', 'LIKE', '%credit%')
                              ->orWhereColumn('ms_mobile_sales.amount_paid', '<', 'ms_mobile_sales.total_amount');
                        });
                });
            })
            ->select(
                'ms_mobile_sales.id',
                'ms_mobile_sales.created_at',
                'ms_mobile_sales.invoice_number as ref_no',
                DB::raw("CONCAT({$prefix}ms_mobile_devices.brand, ' ', {$prefix}ms_mobile_devices.model, ' (IMEI: ', {$prefix}ms_mobile_devices.imei_1, ')') as particulars"),
                'ms_mobile_sales.total_amount as billed_amount',
                'ms_mobile_sales.amount_paid as paid_amount',
                'ms_mobile_sales.udhari_amount as due_amount',
                'ms_mobile_sales.payment_mode',
                'ms_mobile_sales.status',
                'ms_mobile_sales.voided_at',
                DB::raw("'mobile_sale' as entry_type")
            )
            ->get();

        $mobEntries = collect();
        foreach ($mobileSales as $ms) {
            $isVoidedMob = in_array($ms->status ?? '', ['voided', 'returned']);
            $mobParticulars = $ms->particulars . ($isVoidedMob ? ' [Full Invoice Returned]' : '');
            $mobEntries->push((object) [
                'id'            => 'mob_' . $ms->id,
                'created_at'    => $ms->created_at,
                'sort_order'    => 0,
                'ref_no'        => $ms->ref_no,
                'particulars'   => $mobParticulars,
                'item_name'     => $mobParticulars,
                'description'   => $isVoidedMob ? 'Full invoice returned & reversed in khata' : '',
                'billed_amount' => (float) $ms->billed_amount,
                'paid_amount'   => 0.00,
                'due_amount'    => (float) $ms->billed_amount,
                'entry_type'    => 'mobile_sale',
                'source'        => 'mobile_sale',
                'source_id'     => (int) $ms->id,
                'payment_mode'  => null,
                'raw_remarks'   => $isVoidedMob ? 'Full Invoice Returned' : '',
                'is_voided'     => $isVoidedMob,
            ]);

            if ((float) $ms->paid_amount > 0) {
                $normMobMode = self::normalizePaymentMode($ms->payment_mode ?? 'cash');
                $mobEntries->push((object) [
                    'id'            => 'mob_' . $ms->id . '_paid',
                    'created_at'    => $ms->created_at,
                    'sort_order'    => 500,
                    'ref_no'        => $ms->ref_no,
                    'particulars'   => "Payment at Counter (Ref #{$ms->ref_no})",
                    'item_name'     => "Payment at Counter",
                    'description'   => "Payment received at billing for #{$ms->ref_no} (" . self::formatPaymentModeLabel($normMobMode) . ")",
                    'billed_amount' => 0.00,
                    'paid_amount'   => (float) $ms->paid_amount,
                    'due_amount'    => 0.00,
                    'entry_type'    => 'payment_received',
                    'source'        => 'counter_payment',
                    'source_id'     => (int) $ms->id,
                    'payment_mode'  => $normMobMode,
                    'raw_remarks'   => "Payment received at billing for #{$ms->ref_no}",
                    'is_voided'     => false,
                ]);
            }

            // If mobile sale was voided/returned, ensure matching reversal credit exists
            if ($isVoidedMob) {
                $mobCredit = max(0.00, (float) $ms->billed_amount - (float) $ms->paid_amount);
                $existingRev = $getExistingReversals($ms->ref_no);
                $missingMobRev = round($mobCredit - $existingRev, 2);

                if ($missingMobRev > 0.0001) {
                    $revDate = !empty($ms->voided_at) ? $ms->voided_at : $ms->created_at;
                    $mobEntries->push((object) [
                        'id'            => 'mob_rev_' . $ms->id,
                        'created_at'    => $revDate,
                        'sort_order'    => 998,
                        'ref_no'        => $ms->ref_no,
                        'particulars'   => "Reversal for Returned: {$ms->particulars}",
                        'item_name'     => "Reversal for Returned Sale #{$ms->ref_no}",
                        'description'   => "Full invoice returned & reversed in khata",
                        'billed_amount' => 0.00,
                        'paid_amount'   => $missingMobRev,
                        'due_amount'    => 0.00,
                        'entry_type'    => 'adjustment',
                        'source'        => 'return_reversal',
                        'source_id'     => (int) $ms->id,
                        'payment_mode'  => 'reversal',
                        'raw_remarks'   => "Reversal for Returned: {$ms->particulars} (Sale #{$ms->ref_no})",
                        'is_voided'     => false,
                    ]);
                }
            }
        }

        // 3. Accessory Sales (Expand each line item separately; include voided/returned sales that had udhari recorded)
        $accSales = DB::table('ms_accessory_sales')
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.customer_id', $customerId)
            ->where(function ($q) {
                $q->where(function ($activeQ) {
                    $activeQ->whereNotIn('ms_accessory_sales.status', ['voided', 'returned', 'partially_returned'])
                        ->where(function ($w) {
                            $w->where('ms_accessory_sales.udhari_amount', '>', 0)
                              ->orWhereColumn('ms_accessory_sales.amount_paid', '<', 'ms_accessory_sales.total_amount');
                        });
                })->orWhere(function ($retQ) {
                    $retQ->whereIn('ms_accessory_sales.status', ['voided', 'returned', 'partially_returned'])
                        ->where(function ($w) {
                            $w->where('ms_accessory_sales.udhari_amount', '>', 0)
                              ->orWhere('ms_accessory_sales.refund_amount', '>', 0)
                              ->orWhere('ms_accessory_sales.payment_mode', 'LIKE', '%udhari%')
                              ->orWhere('ms_accessory_sales.payment_mode', 'LIKE', '%credit%')
                              ->orWhere('ms_accessory_sales.payment_mode', 'LIKE', '%split%')
                              ->orWhereColumn('ms_accessory_sales.amount_paid', '<', 'ms_accessory_sales.total_amount');
                        });
                });
            })
            ->select(
                'ms_accessory_sales.id',
                'ms_accessory_sales.created_at',
                'ms_accessory_sales.invoice_number as ref_no',
                'ms_accessory_sales.total_amount as billed_amount',
                'ms_accessory_sales.amount_paid as paid_amount',
                'ms_accessory_sales.udhari_amount as due_amount',
                'ms_accessory_sales.payment_mode',
                'ms_accessory_sales.status',
                'ms_accessory_sales.refund_amount',
                'ms_accessory_sales.voided_at',
                DB::raw("'accessory_sale' as entry_type")
            )
            ->get();

        $accEntries = collect();

        if ($accSales->isNotEmpty()) {
            $saleIds = $accSales->pluck('id')->toArray();
            $itemsGrouped = DB::table('ms_accessory_sale_items')
                ->where('company_id', $companyId)
                ->whereIn('accessory_sale_id', $saleIds)
                ->select('id', 'accessory_sale_id', 'part_name', 'quantity', 'unit_price', 'line_total', 'created_at')
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy('accessory_sale_id');

            foreach ($accSales as $sale) {
                $isVoidedSale = in_array($sale->status ?? '', ['voided', 'returned']);
                $items = $itemsGrouped[$sale->id] ?? collect();
                $saleBilledTotal = 0.00;

                if ($items->isNotEmpty()) {
                    foreach ($items as $idx => $it) {
                        $itemName = $it->part_name . ($it->quantity > 1 ? " (x{$it->quantity})" : "");
                        if ($isVoidedSale) {
                            $itemName .= " [Full Invoice Returned]";
                        }
                        $itemBilled = (float) ($it->line_total > 0 ? $it->line_total : ($it->unit_price * $it->quantity));
                        $saleBilledTotal += $itemBilled;

                        $accEntries->push((object) [
                            'id'            => 'acc_' . $sale->id . '_' . $it->id,
                            'created_at'    => $sale->created_at,
                            'sort_order'    => $idx,
                            'ref_no'        => $sale->ref_no,
                            'particulars'   => $itemName,
                            'item_name'     => $itemName,
                            'description'   => $isVoidedSale ? 'Full invoice returned & reversed in khata' : '',
                            'billed_amount' => $itemBilled,
                            'paid_amount'   => 0.00,
                            'due_amount'    => $itemBilled,
                            'entry_type'    => 'accessory_sale',
                            'source'        => 'accessory_sale',
                            'source_id'     => (int) $sale->id,
                            'payment_mode'  => null,
                            'raw_remarks'   => $isVoidedSale ? 'Full Invoice Returned' : '',
                            'is_voided'     => $isVoidedSale,
                        ]);
                    }

                    // If upfront payment was made on this sale, record as a separate line
                    if ((float) $sale->paid_amount > 0) {
                        $normAccMode = self::normalizePaymentMode($sale->payment_mode ?? 'cash');
                        $accEntries->push((object) [
                            'id'            => 'acc_' . $sale->id . '_paid',
                            'created_at'    => $sale->created_at,
                            'sort_order'    => 500,
                            'ref_no'        => $sale->ref_no,
                            'particulars'   => "Payment at Counter (Ref #{$sale->ref_no})",
                            'item_name'     => "Payment at Counter",
                            'description'   => "Payment received at billing for #{$sale->ref_no} (" . self::formatPaymentModeLabel($normAccMode) . ")",
                            'billed_amount' => 0.00,
                            'paid_amount'   => (float) $sale->paid_amount,
                            'due_amount'    => 0.00,
                            'entry_type'    => 'payment_received',
                            'source'        => 'counter_payment',
                            'source_id'     => (int) $sale->id,
                            'payment_mode'  => $normAccMode,
                            'raw_remarks'   => "Payment received at billing for #{$sale->ref_no}",
                            'is_voided'     => false,
                        ]);
                    }
                } else {
                    $itemName = 'Accessory Purchase' . ($isVoidedSale ? ' [Full Invoice Returned]' : '');
                    $saleBilledTotal = (float) $sale->billed_amount;
                    $accEntries->push((object) [
                        'id'            => 'acc_' . $sale->id,
                        'created_at'    => $sale->created_at,
                        'sort_order'    => 0,
                        'ref_no'        => $sale->ref_no,
                        'particulars'   => $itemName,
                        'item_name'     => $itemName,
                        'description'   => $isVoidedSale ? 'Full invoice returned & reversed in khata' : '',
                        'billed_amount' => $saleBilledTotal,
                        'paid_amount'   => (float) $sale->paid_amount,
                        'due_amount'    => (float) $sale->due_amount,
                        'entry_type'    => 'accessory_sale',
                        'source'        => 'accessory_sale',
                        'source_id'     => (int) $sale->id,
                        'payment_mode'  => null,
                        'raw_remarks'   => $isVoidedSale ? 'Full Invoice Returned' : '',
                        'is_voided'     => $isVoidedSale,
                    ]);
                }

                // If sale was voided/returned, ensure matching reversal credit exists in Khata
                if ($isVoidedSale) {
                    $creditUdhari = max(0.00, $saleBilledTotal - (float) $sale->paid_amount);
                    $existingRev = $getExistingReversals($sale->ref_no);
                    $missingAccRev = round($creditUdhari - $existingRev, 2);

                    if ($missingAccRev > 0.0001) {
                        $revTimestamp = !empty($sale->voided_at) ? $sale->voided_at : $sale->created_at;
                        $itemSummary = $items->map(function ($it) {
                            return ($it->quantity > 1 ? "{$it->quantity}x " : "") . $it->part_name;
                        })->filter()->join(', ');

                        $accEntries->push((object) [
                            'id'            => 'acc_rev_' . $sale->id,
                            'created_at'    => $revTimestamp,
                            'sort_order'    => 998,
                            'ref_no'        => $sale->ref_no,
                            'particulars'   => "Reversal for Returned Items on Sale #{$sale->ref_no}",
                            'item_name'     => "Reversal for Returned Items on Sale #{$sale->ref_no}",
                            'description'   => $itemSummary ? "Items: {$itemSummary}" : "Full invoice returned & reversed in khata",
                            'billed_amount' => 0.00,
                            'paid_amount'   => $missingAccRev,
                            'due_amount'    => 0.00,
                            'entry_type'    => 'adjustment',
                            'source'        => 'return_reversal',
                            'source_id'     => (int) $sale->id,
                            'payment_mode'  => 'reversal',
                            'raw_remarks'   => "Reversal for Returned: " . ($itemSummary ?: "Sale #{$sale->ref_no}") . " (Sale #{$sale->ref_no})",
                            'is_voided'     => false,
                        ]);
                    }
                } elseif ((float)($sale->refund_amount ?? 0) > 0) {
                    $refundAmt = (float) $sale->refund_amount;
                    $existingRev = $getExistingReversals($sale->ref_no);
                    $missingPartRev = round($refundAmt - $existingRev, 2);
                    if ($missingPartRev > 0.0001) {
                        $revTimestamp = !empty($sale->voided_at) ? $sale->voided_at : $sale->created_at;
                        $accEntries->push((object) [
                            'id'            => 'acc_part_rev_' . $sale->id,
                            'created_at'    => $revTimestamp,
                            'sort_order'    => 998,
                            'ref_no'        => $sale->ref_no,
                            'particulars'   => "Reversal for Returned Items on Sale #{$sale->ref_no}",
                            'item_name'     => "Reversal for Returned Items on Sale #{$sale->ref_no}",
                            'description'   => "Partial return credited to customer khata",
                            'billed_amount' => 0.00,
                            'paid_amount'   => $missingPartRev,
                            'due_amount'    => 0.00,
                            'entry_type'    => 'adjustment',
                            'source'        => 'return_reversal',
                            'source_id'     => (int) $sale->id,
                            'payment_mode'  => 'reversal',
                            'raw_remarks'   => "Reversal for Returned Items on Sale #{$sale->ref_no}",
                            'is_voided'     => false,
                        ]);
                    }
                }
            }
        }

        // Merge and sort chronologically
        $allEntries = $mobEntries->concat($accEntries)->concat($khataTxs)->sortBy(function ($entry) {
            $ts = strtotime($entry->created_at);
            $order = isset($entry->sort_order) ? (int)$entry->sort_order : 0;
            return sprintf('%012d_%04d', $ts, $order);
        })->values();

        $runningBalance = 0.00;
        $totalBilled = 0.00;
        $totalPaid = 0.00;

        $ledger = $allEntries->map(function ($entry) use (&$runningBalance, &$totalBilled, &$totalPaid) {
            $billed = (float) $entry->billed_amount;
            $paid = (float) $entry->paid_amount;
            
            $totalBilled += $billed;
            $totalPaid += $paid;

            if ($entry->entry_type === 'adjustment' || $entry->entry_type === 'payment_received' || $entry->entry_type === 'refund_credit') {
                $runningBalance = max(0.00, $runningBalance - $paid);
            } else {
                $runningBalance = max(0.00, $runningBalance + ($billed - $paid));
            }

            return (object) [
                'id'           => $entry->id,
                'created_at'   => $entry->created_at,
                'date'         => date('d/m/Y', strtotime($entry->created_at)),
                'datetime'     => date('d M Y, h:i A', strtotime($entry->created_at)),
                'ref_no'       => $entry->ref_no,
                'particulars'  => $entry->particulars,
                'item_name'    => $entry->item_name ?? $entry->particulars,
                'description'  => $entry->description ?? '',
                'billed'       => $billed,
                'paid'         => $paid,
                'balance_left' => $runningBalance,
                'entry_type'   => $entry->entry_type,
                'source'       => $entry->source ?? 'unknown',
                'source_id'    => $entry->source_id ?? null,
                'payment_mode' => $entry->payment_mode ?? null,
                'raw_remarks'  => $entry->raw_remarks ?? '',
            ];
        });

        // Resolve filter mode: 'active' (default), 'last_payment', 'this_month', 'last_30_days', 'all', 'custom'
        $filter = strtolower(trim((string) ($filters['filter'] ?? 'active')));
        if (!in_array($filter, ['active', 'last_payment', 'this_month', 'last_30_days', 'all', 'custom'])) {
            $filter = 'active';
        }

        $now = now();
        $startDate = null;
        $endDate = null;
        $periodLabel = 'Active Unsettled Dues';

        if ($filter === 'last_payment') {
            $periodLabel = 'From Last Payment';
        } elseif ($filter === 'this_month') {
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d 00:00:00');
            $endDate   = $now->copy()->endOfMonth()->format('Y-m-d 23:59:59');
            $periodLabel = 'This Month (' . $now->format('M Y') . ')';
        } elseif ($filter === 'last_30_days') {
            $startDate = $now->copy()->subDays(30)->startOfDay()->format('Y-m-d 00:00:00');
            $endDate   = $now->copy()->endOfDay()->format('Y-m-d 23:59:59');
            $periodLabel = 'Last 30 Days (' . $now->copy()->subDays(30)->format('d M') . ' — ' . $now->format('d M Y') . ')';
        } elseif ($filter === 'custom') {
            $rawFrom = $filters['from'] ?? null;
            $rawTo   = $filters['to'] ?? null;
            if ($rawFrom && $rawTo) {
                $startDate = date('Y-m-d 00:00:00', strtotime($rawFrom));
                $endDate   = date('Y-m-d 23:59:59', strtotime($rawTo));
                $periodLabel = date('d M Y', strtotime($rawFrom)) . ' — ' . date('d M Y', strtotime($rawTo));
            } else {
                $filter = 'active';
                $periodLabel = 'Active Unsettled Dues';
            }
        } elseif ($filter === 'all') {
            $periodLabel = 'All Time History';
        }

        $openingBalance = 0.00;
        $hasPriorHistory = false;
        $priorSettledCount = 0;
        $displayLedger = collect();

        if ($filter === 'last_payment') {
            // Collect all entries where a repayment, counter payment, or credit was made (paid > 0)
            $paymentIndices = [];
            foreach ($ledger as $idx => $entry) {
                if ($entry->paid > 0.0001) {
                    $paymentIndices[] = $idx;
                }
            }

            if (!empty($paymentIndices)) {
                $latestPaymentIdx = end($paymentIndices);
                $lastPaymentEntry = $ledger[$latestPaymentIdx];
                $periodLabel = 'From Last Payment (' . date('d M Y', strtotime($lastPaymentEntry->created_at)) . ')';

                // Check transactions after the latest payment
                $purchasesAfter = $ledger->slice($latestPaymentIdx + 1)->where('billed', '>', 0);
                $billedAfter = (float) $purchasesAfter->sum('billed');

                // Check transactions before the latest payment
                $purchasesBefore = $ledger->slice(0, $latestPaymentIdx)->where('billed', '>', 0);
                $billedBefore = (float) $purchasesBefore->sum('billed');

                $clearedPrevious = ($ledger[$latestPaymentIdx]->balance_left <= 0.0001);

                $sliceIndex = 0;

                // Case A: If substantial new purchases occurred AFTER the latest payment
                // (e.g. customer settled or made a payment, and then subsequently bought new items)
                if ($billedAfter > 0 && ($clearedPrevious || $billedBefore <= 0.0001 || $billedAfter >= $billedBefore)) {
                    // Start from the latest payment entry so customer sees that payment and subsequent purchases
                    $sliceIndex = $latestPaymentIdx;
                } else {
                    // Case B: Purchases occurred BEFORE (or up to) the latest payment
                    // (e.g. Arvind Jaiswal who bought items on credit and then made a repayment)
                    // The statement must include the items sold that this payment was made towards!
                    if (count($paymentIndices) >= 2) {
                        $prevPaymentIdx = $paymentIndices[count($paymentIndices) - 2];
                        
                        // Check if there are purchases between prevPaymentIdx and latestPaymentIdx
                        $purchasesBetween = $ledger->slice($prevPaymentIdx + 1, $latestPaymentIdx - $prevPaymentIdx)->where('billed', '>', 0);
                        
                        if ($purchasesBetween->count() > 0) {
                            $sliceIndex = $prevPaymentIdx + 1;
                        } else {
                            // Multiple repayments with no items between (e.g. installment payments)
                            // Look further back to find where the debt cycle actually started
                            $foundSlice = null;
                            for ($k = count($paymentIndices) - 2; $k >= 0; $k--) {
                                $candidateIdx = $paymentIndices[$k];
                                $prevBefore = $k > 0 ? $paymentIndices[$k - 1] : -1;
                                $salesInK = $ledger->slice($prevBefore + 1, $candidateIdx - ($prevBefore + 1))->where('billed', '>', 0);
                                if ($salesInK->count() > 0) {
                                    $foundSlice = $prevBefore + 1;
                                    break;
                                }
                            }
                            $sliceIndex = $foundSlice !== null ? $foundSlice : 0;
                        }
                    } else {
                        // Only 1 payment in customer's entire history (e.g. Arvind Jaiswal)
                        // Look for the last time balance was 0 before this payment; if none, start from inception (0)
                        $lastZeroBefore = null;
                        for ($i = 0; $i < $latestPaymentIdx; $i++) {
                            if ($ledger[$i]->balance_left <= 0.0001) {
                                $lastZeroBefore = $i;
                            }
                        }

                        $sliceIndex = ($lastZeroBefore !== null) ? ($lastZeroBefore + 1) : 0;
                    }
                }

                // Ensure sliceIndex is within bounds [0, $latestPaymentIdx]
                $sliceIndex = max(0, min($sliceIndex, $latestPaymentIdx));

                // Prior entries before sliceIndex
                $priorEntries = $ledger->slice(0, $sliceIndex)->values();
                if ($priorEntries->isNotEmpty()) {
                    $openingBalance = (float) $priorEntries->last()->balance_left;
                    $hasPriorHistory = true;
                    $priorSettledCount = $priorEntries->count();
                } else {
                    $openingBalance = 0.00;
                    $hasPriorHistory = false;
                    $priorSettledCount = 0;
                }

                $periodEntries = $ledger->slice($sliceIndex)->values();

                if ($openingBalance > 0.0001) {
                    $firstPeriodEntry = $periodEntries->first() ?? $lastPaymentEntry;
                    $openingEntry = (object) [
                        'id'           => 'opening_balance_forward',
                        'created_at'   => $firstPeriodEntry->created_at,
                        'date'         => date('d/m/Y', strtotime($firstPeriodEntry->created_at)),
                        'datetime'     => date('d M Y, h:i A', strtotime($firstPeriodEntry->created_at)),
                        'ref_no'       => 'OPENING',
                        'particulars'  => 'Opening Balance Brought Forward',
                        'item_name'    => 'Opening Balance Brought Forward',
                        'description'  => 'Balance accumulated prior to ' . date('d M Y', strtotime($firstPeriodEntry->created_at)),
                        'billed'       => $openingBalance,
                        'paid'         => 0.00,
                        'balance_left' => $openingBalance,
                        'entry_type'   => 'opening_balance',
                        'is_opening'   => true,
                    ];
                    $displayLedger = collect([$openingEntry])->concat($periodEntries)->values();
                } else {
                    $displayLedger = $periodEntries;
                }

                $totalBilled = (float) $periodEntries->sum('billed');
                $totalPaid   = (float) $periodEntries->sum('paid');
            } else {
                // If customer has never made any payment, fallback to active dues
                $displayLedger = $ledger;
                $hasPriorHistory = false;
                $priorSettledCount = 0;
                $periodLabel = 'Active Dues (No prior repayments)';
                $totalBilled = (float) $ledger->sum('billed');
                $totalPaid   = (float) $ledger->sum('paid');
            }
        } elseif ($filter === 'active') {
            // Option 1: Khatabook Style — Current Unsettled Dues Only
            // Locate the last entry where the running balance was 0.00
            $lastZeroIndex = null;
            foreach ($ledger as $idx => $entry) {
                if ($entry->balance_left <= 0.0001) {
                    $lastZeroIndex = $idx;
                }
            }

            if ($lastZeroIndex !== null && $lastZeroIndex < $ledger->count() - 1) {
                // Has prior settled transactions; return only items after the last zero balance
                $displayLedger = $ledger->slice($lastZeroIndex + 1)->values();
                $hasPriorHistory = true;
                $priorSettledCount = $lastZeroIndex + 1;
            } elseif ($lastZeroIndex !== null && $lastZeroIndex === $ledger->count() - 1) {
                // Customer has completely cleared their account (current balance is 0.00)
                // Show the latest settled transactions as receipt of closure
                $displayLedger = $ledger->slice(max(0, $ledger->count() - 3))->values();
                $hasPriorHistory = $ledger->count() > $displayLedger->count();
                $priorSettledCount = max(0, $ledger->count() - $displayLedger->count());
                $periodLabel = 'Account Fully Settled (Balance: ₹0.00)';
            } else {
                // Customer never reached 0.00 balance in their entire history
                $displayLedger = $ledger;
                $hasPriorHistory = false;
                $priorSettledCount = 0;
            }

            $totalBilled = (float) $displayLedger->sum('billed');
            $totalPaid   = (float) $displayLedger->sum('paid');
        } elseif ($filter === 'all') {
            $displayLedger = $ledger;
            $hasPriorHistory = false;
            $priorSettledCount = 0;
            $totalBilled = (float) $ledger->sum('billed');
            $totalPaid   = (float) $ledger->sum('paid');
        } else {
            // Date-range filtering ('this_month', 'last_30_days', 'custom')
            $startTs = strtotime($startDate);
            $endTs   = strtotime($endDate);

            $priorEntries = $ledger->filter(function ($entry) use ($startTs) {
                return strtotime($entry->created_at) < $startTs;
            })->values();

            $periodEntries = $ledger->filter(function ($entry) use ($startTs, $endTs) {
                $t = strtotime($entry->created_at);
                return $t >= $startTs && $t <= $endTs;
            })->values();

            if ($priorEntries->isNotEmpty()) {
                $openingBalance = (float) $priorEntries->last()->balance_left;
                $hasPriorHistory = true;
                $priorSettledCount = $priorEntries->count();
            }

            if ($openingBalance > 0.0001 || $hasPriorHistory) {
                $openingEntry = (object) [
                    'id'           => 'opening_balance_forward',
                    'created_at'   => $startDate,
                    'date'         => date('d/m/Y', $startTs),
                    'datetime'     => date('d M Y, h:i A', $startTs),
                    'ref_no'       => 'OPENING',
                    'particulars'  => 'Opening Balance Brought Forward',
                    'item_name'    => 'Opening Balance Brought Forward',
                    'description'  => 'Balance accumulated prior to ' . date('d M Y', $startTs),
                    'billed'       => $openingBalance,
                    'paid'         => 0.00,
                    'balance_left' => $openingBalance,
                    'entry_type'   => 'opening_balance',
                    'is_opening'   => true,
                ];
                $displayLedger = collect([$openingEntry])->concat($periodEntries)->values();
            } else {
                $displayLedger = $periodEntries;
            }

            $totalBilled = (float) $periodEntries->sum('billed');
            $totalPaid   = (float) $periodEntries->sum('paid');
        }

        $totalPaidCash = 0.00;
        $totalPaidOnline = 0.00;
        $totalPaidBank = 0.00;
        $totalPaidCheque = 0.00;
        $totalReturnCredits = 0.00;

        foreach ($displayLedger as $entry) {
            if (!empty($entry->is_opening)) {
                continue;
            }
            $pAmt = (float) ($entry->paid ?? 0);
            if ($pAmt <= 0.0001) {
                continue;
            }

            $normMode = self::normalizePaymentMode(
                $entry->payment_mode ?? null,
                ($entry->raw_remarks ?? '') . ' ' . ($entry->description ?? '') . ' ' . ($entry->particulars ?? '')
            );

            if ($normMode === 'reversal') {
                $totalReturnCredits += $pAmt;
            } elseif ($normMode === 'cash') {
                $totalPaidCash += $pAmt;
            } elseif ($normMode === 'upi') {
                $totalPaidOnline += $pAmt;
            } elseif ($normMode === 'bank_transfer') {
                $totalPaidBank += $pAmt;
            } elseif ($normMode === 'cheque') {
                $totalPaidCheque += $pAmt;
            } elseif ($normMode === 'split') {
                $totalPaidOnline += $pAmt;
            } else {
                $totalPaidCash += $pAmt;
            }
        }

        return [
            'customer'            => $customer,
            'ledger'              => $displayLedger,
            'fullLedgerCount'     => $ledger->count(),
            'filteredLedgerCount' => $displayLedger->count(),
            'currentFilter'       => $filter,
            'periodLabel'         => $periodLabel,
            'fromDate'            => $startDate ? date('Y-m-d', strtotime($startDate)) : null,
            'toDate'              => $endDate ? date('Y-m-d', strtotime($endDate)) : null,
            'openingBalance'      => $openingBalance,
            'hasPriorHistory'     => $hasPriorHistory,
            'priorSettledCount'   => $priorSettledCount,
            'totalBilled'         => $totalBilled,
            'totalPaid'           => $totalPaid,
            'totalPaidCash'       => $totalPaidCash,
            'totalPaidOnline'     => $totalPaidOnline,
            'totalPaidBank'       => $totalPaidBank,
            'totalPaidCheque'     => $totalPaidCheque,
            'totalReturnCredits'  => $totalReturnCredits,
            'closingBalance'      => $displayLedger->isNotEmpty()
                ? (float) $displayLedger->last()->balance_left
                : ($ledger->isNotEmpty() ? (float) $ledger->last()->balance_left : (float) ($customer->udhari_balance ?? 0.00)),
        ];
    }

    /**
     * Resolve Mobile Phone Sale details with all joined relations and gifts
     */
    public static function resolvePhoneSaleDetails(int $companyId, int $id): array
    {
        $sale = DB::table('ms_mobile_sales')
            ->leftJoin('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
            ->leftJoin('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone', 'ms_customers.gstin as customer_gstin', 'ms_customers.address as customer_address', 'ms_customers.udhari_balance as current_udhari_balance', 'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.color', 'ms_mobile_devices.ram', 'ms_mobile_devices.storage', 'ms_mobile_devices.imei_1', 'ms_mobile_devices.imei_2', 'ms_mobile_devices.hsn_code', 'ms_mobile_devices.type as device_type', 'ms_mobile_devices.condition_grade')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.id', $id)
            ->first();

        if (!$sale) {
            abort(404, 'Invoice not found');
        }

        $gifts = DB::table('ms_sale_gifts')
            ->leftJoin('ms_parts_inventory', 'ms_sale_gifts.gift_id', '=', 'ms_parts_inventory.id')
            ->where('ms_sale_gifts.sale_id', $id)
            ->select('ms_parts_inventory.name', 'ms_sale_gifts.gift_name', 'ms_sale_gifts.purchase_cost', 'ms_sale_gifts.qty')
            ->get();
        foreach ($gifts as $g) {
            $g->name = $g->name ?: ($g->gift_name ?: 'Promotional Gift Item');
        }

        $emiProvider = $sale->emi_provider_id ? DB::table('ms_emi_providers')->where('company_id', $companyId)->where('id', $sale->emi_provider_id)->first() : null;
        $amountInWords = self::amountToWords($sale->total_amount);
        $customerStatement = $sale->customer_id ? self::buildCustomerLedgerStatement($companyId, (int) $sale->customer_id) : null;

        return compact('sale', 'gifts', 'emiProvider', 'amountInWords', 'customerStatement');
    }

    /**
     * Resolve Accessory Sale details with customer and line items
     */
    public static function resolveAccessorySaleDetails(int $companyId, int $id): array
    {
        $sale = DB::table('ms_accessory_sales')
            ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
            ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone', 'ms_customers.gstin as customer_gstin', 'ms_customers.address as customer_address', 'ms_customers.udhari_balance as current_udhari_balance')
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.id', $id)
            ->first();

        if (!$sale) {
            abort(404, 'Accessory invoice not found.');
        }

        $items = DB::table('ms_accessory_sale_items')
            ->where('company_id', $companyId)
            ->where('accessory_sale_id', $id)
            ->get();

        // Resolve Returned Items & Refund Calculations
        $returnedItems = [];
        $totalRefund = 0.0;

        if (!empty($sale->return_details)) {
            $batches = is_string($sale->return_details) ? json_decode($sale->return_details, true) : $sale->return_details;
            if (is_array($batches)) {
                foreach ($batches as $batch) {
                    $bDate = $batch['date'] ?? null;
                    $bReason = $batch['reason'] ?? 'Customer Return';
                    foreach ($batch['items'] ?? [] as $bItem) {
                        $unitP = (float) ($bItem['unit_price'] ?? 0);
                        $qty = (int) ($bItem['quantity'] ?? $bItem['qty'] ?? 1);
                        $lTot = (float) ($bItem['line_total'] ?? ($unitP * $qty));
                        $returnedItems[] = (object) [
                            'item_id'     => $bItem['item_id'] ?? null,
                            'part_name'   => $bItem['part_name'] ?? 'Returned Item',
                            'quantity'    => $qty,
                            'unit_price'  => $unitP,
                            'line_total'  => $lTot,
                            'reason'      => $bReason,
                            'return_date' => $bDate,
                        ];
                        $totalRefund += $lTot;
                    }
                }
            }
        }

        // Fallback: If no return_details JSON, extract from items returned_qty
        if (empty($returnedItems)) {
            foreach ($items as $it) {
                $rQty = (int) ($it->returned_qty ?? 0);
                if ($rQty <= 0 && $sale->status === 'voided') {
                    $rQty = $it->quantity;
                }
                if ($rQty > 0) {
                    $unitP = (float) $it->unit_price > 0 ? (float) $it->unit_price : ((float) $it->line_total / max(1, $it->quantity));
                    $lTot = round($unitP * $rQty, 2);
                    $returnedItems[] = (object) [
                        'item_id'     => $it->id,
                        'part_name'   => $it->part_name,
                        'quantity'    => $rQty,
                        'unit_price'  => $unitP,
                        'line_total'  => $lTot,
                        'reason'      => $sale->void_reason ?: 'Customer Return',
                        'return_date' => $sale->voided_at,
                    ];
                    $totalRefund += $lTot;
                }
            }
        }

        if ($totalRefund <= 0 && (float) ($sale->refund_amount ?? 0) > 0) {
            $totalRefund = (float) $sale->refund_amount;
        }
        if ($totalRefund <= 0 && $sale->status === 'voided') {
            $totalRefund = (float) $sale->total_amount;
        }

        $totalRefund = round($totalRefund, 2);
        $netPayable = max(0.00, round((float) $sale->total_amount - $totalRefund, 2));

        $amountInWords = self::amountToWords($netPayable > 0 ? $netPayable : $sale->total_amount);
        $customerStatement = $sale->customer_id ? self::buildCustomerLedgerStatement($companyId, (int) $sale->customer_id) : null;

        return compact('sale', 'items', 'returnedItems', 'totalRefund', 'netPayable', 'amountInWords', 'customerStatement');
    }

    /**
     * Resolve Purchase Order details with supplier and line items
     */
    public static function resolvePurchaseOrderDetails(int $companyId, int $id): ?array
    {
        $po = DB::table('ms_purchase_orders')
            ->leftJoin('ms_suppliers', 'ms_purchase_orders.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_purchase_orders.*', 'ms_suppliers.name as supplier_name', 'ms_suppliers.phone as supplier_phone', 'ms_suppliers.gstin as supplier_gstin', 'ms_suppliers.address as supplier_address')
            ->where('ms_purchase_orders.company_id', $companyId)
            ->where('ms_purchase_orders.id', $id)
            ->first();

        if (!$po) {
            return null;
        }

        $items = DB::table('ms_purchase_order_items')->where('purchase_order_id', $po->id)->get();
        $amountInWords = self::amountToWords($po->total_amount);

        return compact('po', 'items', 'amountInWords');
    }

    /**
     * Resolve a summary of returned item names for a given sale invoice.
     */
    public static function resolveReturnedItemSummary(int $companyId, string $invoiceNumber, float $refundAmount = 0.0): ?string
    {
        $cleanInv = trim(str_replace('#', '', $invoiceNumber));
        if (empty($cleanInv)) {
            return null;
        }

        // 1. Try Accessory Sales
        $accSale = DB::table('ms_accessory_sales')
            ->where('company_id', $companyId)
            ->where('invoice_number', $cleanInv)
            ->first();

        if ($accSale) {
            $returnedItems = [];

            // A. Check return_details JSON
            if (!empty($accSale->return_details)) {
                $batches = is_string($accSale->return_details) ? json_decode($accSale->return_details, true) : $accSale->return_details;
                if (is_array($batches)) {
                    $targetBatch = null;
                    if ($refundAmount > 0.001) {
                        foreach ($batches as $b) {
                            if (abs((float)($b['refund_amount'] ?? 0) - $refundAmount) < 0.05) {
                                $targetBatch = $b;
                                break;
                            }
                        }
                    }
                    if (!$targetBatch && !empty($batches)) {
                        $targetBatch = end($batches);
                    }

                    if ($targetBatch && !empty($targetBatch['items']) && is_array($targetBatch['items'])) {
                        foreach ($targetBatch['items'] as $bItem) {
                            $name = trim((string)($bItem['part_name'] ?? ''));
                            $qty = (int)($bItem['quantity'] ?? $bItem['qty'] ?? 1);
                            if (!empty($name)) {
                                $returnedItems[] = ($qty > 1 ? "{$qty}x " : "") . $name;
                            }
                        }
                    }
                }
            }

            // B. Fallback to ms_accessory_sale_items
            if (empty($returnedItems)) {
                $saleItems = DB::table('ms_accessory_sale_items')
                    ->where('company_id', $companyId)
                    ->where('accessory_sale_id', $accSale->id)
                    ->get();

                // Look for items with returned_qty > 0
                $itemsWithReturnedQty = $saleItems->filter(fn($it) => (int)($it->returned_qty ?? 0) > 0);
                if ($itemsWithReturnedQty->isNotEmpty()) {
                    if ($refundAmount > 0.001) {
                        $matchedItem = $itemsWithReturnedQty->first(fn($it) => abs((float)($it->returned_amount ?? 0) - $refundAmount) < 0.05);
                        if ($matchedItem) {
                            $qty = (int)$matchedItem->returned_qty;
                            $returnedItems[] = ($qty > 1 ? "{$qty}x " : "") . $matchedItem->part_name;
                        }
                    }

                    if (empty($returnedItems)) {
                        foreach ($itemsWithReturnedQty as $it) {
                            $qty = (int)$it->returned_qty;
                            $returnedItems[] = ($qty > 1 ? "{$qty}x " : "") . $it->part_name;
                        }
                    }
                } elseif (in_array($accSale->status, ['voided', 'partially_returned'])) {
                    if ($refundAmount > 0.001) {
                        $matchedItem = $saleItems->first(fn($it) => abs((float)$it->line_total - $refundAmount) < 0.05 || abs((float)$it->unit_price - $refundAmount) < 0.05);
                        if ($matchedItem) {
                            $qty = (int)$matchedItem->quantity;
                            $returnedItems[] = ($qty > 1 ? "{$qty}x " : "") . $matchedItem->part_name;
                        }
                    }

                    if (empty($returnedItems)) {
                        foreach ($saleItems as $it) {
                            $qty = (int)$it->quantity;
                            $returnedItems[] = ($qty > 1 ? "{$qty}x " : "") . $it->part_name;
                        }
                    }
                }
            }

            if (!empty($returnedItems)) {
                return implode(', ', array_unique($returnedItems));
            }
        }

        // 2. Try Mobile Sales
        $mobSale = DB::table('ms_mobile_sales')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.invoice_number', $cleanInv)
            ->select('ms_mobile_devices.brand', 'ms_mobile_devices.model')
            ->first();

        if ($mobSale) {
            return trim("{$mobSale->brand} {$mobSale->model}");
        }

        return null;
    }
}
