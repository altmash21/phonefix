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
                'closingBalance'      => 0.00,
            ];
        }

        $prefix = DB::getTablePrefix();

        // 1. Mobile Sales (Only include sales where a balance was left)
        $mobileSales = DB::table('ms_mobile_sales')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
            ->where('ms_mobile_sales.company_id', $companyId)
            ->where('ms_mobile_sales.customer_id', $customerId)
            ->where('ms_mobile_sales.status', '!=', 'voided')
            ->where(function ($q) {
                $q->where('ms_mobile_sales.udhari_amount', '>', 0)
                  ->orWhereColumn('ms_mobile_sales.amount_paid', '<', 'ms_mobile_sales.total_amount');
            })
            ->select(
                'ms_mobile_sales.id',
                'ms_mobile_sales.created_at',
                'ms_mobile_sales.invoice_number as ref_no',
                DB::raw("CONCAT({$prefix}ms_mobile_devices.brand, ' ', {$prefix}ms_mobile_devices.model, ' (IMEI: ', {$prefix}ms_mobile_devices.imei_1, ')') as particulars"),
                'ms_mobile_sales.total_amount as billed_amount',
                'ms_mobile_sales.amount_paid as paid_amount',
                'ms_mobile_sales.udhari_amount as due_amount',
                DB::raw("'mobile_sale' as entry_type")
            )
            ->get();

        $mobEntries = collect();
        foreach ($mobileSales as $ms) {
            $mobEntries->push((object) [
                'id'            => 'mob_' . $ms->id,
                'created_at'    => $ms->created_at,
                'sort_order'    => 0,
                'ref_no'        => $ms->ref_no,
                'particulars'   => $ms->particulars,
                'item_name'     => $ms->particulars,
                'description'   => '',
                'billed_amount' => (float) $ms->billed_amount,
                'paid_amount'   => 0.00,
                'due_amount'    => (float) $ms->billed_amount,
                'entry_type'    => 'mobile_sale',
            ]);

            if ((float) $ms->paid_amount > 0) {
                $mobEntries->push((object) [
                    'id'            => 'mob_' . $ms->id . '_paid',
                    'created_at'    => $ms->created_at,
                    'sort_order'    => 999,
                    'ref_no'        => $ms->ref_no,
                    'particulars'   => "Payment at Counter (Ref #{$ms->ref_no})",
                    'item_name'     => "Payment at Counter",
                    'description'   => "Advance / Cash payment received at billing for #{$ms->ref_no}",
                    'billed_amount' => 0.00,
                    'paid_amount'   => (float) $ms->paid_amount,
                    'due_amount'    => 0.00,
                    'entry_type'    => 'payment_received',
                ]);
            }
        }

        // 2. Accessory Sales (Expand each line item separately)
        $accSales = DB::table('ms_accessory_sales')
            ->where('ms_accessory_sales.company_id', $companyId)
            ->where('ms_accessory_sales.customer_id', $customerId)
            ->where('ms_accessory_sales.status', '!=', 'voided')
            ->where(function ($q) {
                $q->where('ms_accessory_sales.udhari_amount', '>', 0)
                  ->orWhereColumn('ms_accessory_sales.amount_paid', '<', 'ms_accessory_sales.total_amount');
            })
            ->select(
                'ms_accessory_sales.id',
                'ms_accessory_sales.created_at',
                'ms_accessory_sales.invoice_number as ref_no',
                'ms_accessory_sales.total_amount as billed_amount',
                'ms_accessory_sales.amount_paid as paid_amount',
                'ms_accessory_sales.udhari_amount as due_amount',
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
                $items = $itemsGrouped[$sale->id] ?? collect();
                if ($items->isNotEmpty()) {
                    foreach ($items as $idx => $it) {
                        $itemName = $it->part_name . ($it->quantity > 1 ? " (x{$it->quantity})" : "");
                        $itemBilled = (float) ($it->line_total > 0 ? $it->line_total : ($it->unit_price * $it->quantity));

                        $accEntries->push((object) [
                            'id'            => 'acc_' . $sale->id . '_' . $it->id,
                            'created_at'    => $sale->created_at,
                            'sort_order'    => $idx,
                            'ref_no'        => $sale->ref_no,
                            'particulars'   => $itemName,
                            'item_name'     => $itemName,
                            'description'   => '',
                            'billed_amount' => $itemBilled,
                            'paid_amount'   => 0.00,
                            'due_amount'    => $itemBilled,
                            'entry_type'    => 'accessory_sale',
                        ]);
                    }

                    // If upfront payment was made on this sale, record as a separate line
                    if ((float) $sale->paid_amount > 0) {
                        $accEntries->push((object) [
                            'id'            => 'acc_' . $sale->id . '_paid',
                            'created_at'    => $sale->created_at,
                            'sort_order'    => 999,
                            'ref_no'        => $sale->ref_no,
                            'particulars'   => "Payment at Counter (Ref #{$sale->ref_no})",
                            'item_name'     => "Payment at Counter",
                            'description'   => "Payment received at billing for #{$sale->ref_no}",
                            'billed_amount' => 0.00,
                            'paid_amount'   => (float) $sale->paid_amount,
                            'due_amount'    => 0.00,
                            'entry_type'    => 'payment_received',
                        ]);
                    }
                } else {
                    $accEntries->push((object) [
                        'id'            => 'acc_' . $sale->id,
                        'created_at'    => $sale->created_at,
                        'sort_order'    => 0,
                        'ref_no'        => $sale->ref_no,
                        'particulars'   => 'Accessory Purchase',
                        'item_name'     => 'Accessory Purchase',
                        'description'   => '',
                        'billed_amount' => (float) $sale->billed_amount,
                        'paid_amount'   => (float) $sale->paid_amount,
                        'due_amount'    => (float) $sale->due_amount,
                        'entry_type'    => 'accessory_sale',
                    ]);
                }
            }
        }

        // 3. Khata Repayments, Old Udhar / Opening Balances & Adjustments
        $khataTxs = DB::table('ms_customer_khata_transactions')
            ->where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('type', '!=', 'udhari_sale')
            ->get()
            ->map(function ($tx) {
                $isDebit = in_array($tx->type, ['opening_balance', 'old_udhar', 'debit_adjustment']);
                $rawRemarks = trim((string) ($tx->remarks ?? ''));
                $hasCustomDesc = !empty($rawRemarks) && !in_array(strtolower($rawRemarks), ['old udhar / opening balance', 'old udhar', 'opening balance']);
                
                if ($isDebit) {
                    $itemDesc = $hasCustomDesc ? "Old Udhar: {$rawRemarks}" : 'Old Udhar / Opening Balance';
                } else {
                    $itemDesc = !empty($rawRemarks) ? $rawRemarks : 'Payment / Settlement Received';
                }

                return (object) [
                    'id'            => $tx->id,
                    'created_at'    => $tx->created_at,
                    'sort_order'    => 500,
                    'ref_no'        => $tx->reference_no ?: ($isDebit ? 'OLD-UDHAR' : ('KHATA-' . $tx->id)),
                    'particulars'   => $itemDesc,
                    'item_name'     => $itemDesc,
                    'description'   => $rawRemarks,
                    'billed_amount' => $isDebit ? (float) $tx->amount : 0.00,
                    'paid_amount'   => $isDebit ? 0.00 : (float) $tx->amount,
                    'due_amount'    => $isDebit ? (float) $tx->amount : 0.00,
                    'entry_type'    => $tx->type,
                ];
            });

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
            // Find the last entry where a payment was received (paid > 0)
            $lastPaymentIndex = null;
            foreach ($ledger as $idx => $entry) {
                if ($entry->paid > 0.0001) {
                    $lastPaymentIndex = $idx;
                }
            }

            if ($lastPaymentIndex !== null) {
                $lastPaymentEntry = $ledger[$lastPaymentIndex];
                $periodLabel = 'From Last Payment (' . date('d M Y', strtotime($lastPaymentEntry->created_at)) . ')';

                // Prior entries before the last payment
                $priorEntries = $ledger->slice(0, $lastPaymentIndex)->values();
                if ($priorEntries->isNotEmpty()) {
                    $openingBalance = (float) $priorEntries->last()->balance_left;
                    $hasPriorHistory = true;
                    $priorSettledCount = $priorEntries->count();
                } else {
                    $openingBalance = 0.00;
                    $hasPriorHistory = false;
                    $priorSettledCount = 0;
                }

                $periodEntries = $ledger->slice($lastPaymentIndex)->values();

                if ($openingBalance > 0.0001 || $hasPriorHistory) {
                    $openingEntry = (object) [
                        'id'           => 'opening_balance_forward',
                        'created_at'   => $lastPaymentEntry->created_at,
                        'date'         => date('d/m/Y', strtotime($lastPaymentEntry->created_at)),
                        'datetime'     => date('d M Y, h:i A', strtotime($lastPaymentEntry->created_at)),
                        'ref_no'       => 'OPENING',
                        'particulars'  => 'Opening Balance Brought Forward',
                        'item_name'    => 'Opening Balance Brought Forward',
                        'description'  => 'Balance accumulated prior to last payment on ' . date('d M Y', strtotime($lastPaymentEntry->created_at)),
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
            'closingBalance'      => (float) $customer->udhari_balance,
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
}
