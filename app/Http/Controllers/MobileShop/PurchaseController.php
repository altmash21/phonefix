<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends BaseMobileShopController
{
    /**
     * Purchase Hub — Niche-scoped purchase/intake list.
     * Each role sees ONLY their own niche's purchases and gets only their Add form.
     */
    public function purchaseHub(Request $request)
    {
        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $user      = auth()->user();
        $isAdmin   = in_array($niche, ['admin']) || ($user && ($user->hasRole('admin') || user()->hasRole('store-admin')));

        $canAddPhones       = $user->can('create-purchase-phones');
        $canAddSecondhand   = $user->can('create-purchase-secondhand');
        $canAddAccessories  = $user->can('create-purchase-accessories');
        $canAddCovers       = $user->can('create-purchase-covers');

        // Niche-scoped purchase records
        $purchaseOrders    = collect();
        $newPhonePurchases = collect();
        $buybacks          = collect();
        $batchRestocks     = collect();

        switch ($niche) {
            case 'phones':
                $newPhonePurchases = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'new')
                    ->orderBy('id', 'desc')->get();
                break;

            case 'secondhand':
                $buybacks = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'second_hand')
                    ->orderBy('id', 'desc')->get();
                break;

            case 'accessories':
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->get();
                break;

            case 'covers':
                $coverCats = $this->coverCategories;
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->whereIn('ms_parts_inventory.category', $coverCats)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->get();
                break;

            default: // admin — all purchases
                $purchaseOrders = DB::table('ms_purchase_orders')
                    ->where('company_id', $companyId)
                    ->orderBy('id', 'desc')
                    ->get();
                $newPhonePurchases = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'new')
                    ->orderBy('id', 'desc')->get();
                $buybacks = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('type', 'second_hand')
                    ->orderBy('id', 'desc')->get();
                $batchRestocks = DB::table('ms_parts_inventory_history')
                    ->join('ms_parts_inventory', 'ms_parts_inventory_history.part_id', '=', 'ms_parts_inventory.id')
                    ->select('ms_parts_inventory_history.*', 'ms_parts_inventory.name as part_name', 'ms_parts_inventory.category')
                    ->where('ms_parts_inventory.company_id', $companyId)
                    ->where('ms_parts_inventory_history.type', 'addition')
                    ->orderBy('ms_parts_inventory_history.id', 'desc')->get();
                break;
        }

        // Master Purchase Invoices Registry (Niche-scoped)
        $poQuery = DB::table('ms_purchase_orders')
            ->leftJoin('ms_suppliers', 'ms_purchase_orders.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_purchase_orders.*', 'ms_suppliers.name as supplier_name', 'ms_suppliers.phone as supplier_phone', 'ms_suppliers.gstin as supplier_gstin')
            ->where('ms_purchase_orders.company_id', $companyId);

        if ($niche === 'phones') {
            $poQuery->where(function($q) {
                $q->where('ms_purchase_orders.po_number', 'like', 'PO-PHONES%')
                  ->orWhere('ms_purchase_orders.po_number', 'like', 'PO-2026%');
            });
        } elseif ($niche === 'secondhand') {
            $poQuery->where('ms_purchase_orders.po_number', 'like', 'BUYBACK%');
        } elseif (in_array($niche, ['accessories', 'covers'])) {
            $poQuery->where(function($q) {
                $q->where('ms_purchase_orders.po_number', 'like', 'INV-%')
                  ->orWhere('ms_purchase_orders.po_number', 'like', 'RESTOCK%');
            });
        }

        $purchaseInvoices = $poQuery->orderBy('ms_purchase_orders.id', 'desc')->get();

        // Eager-load items for all purchase invoices
        $poIds = $purchaseInvoices->pluck('id')->toArray();
        $itemsByPo = DB::table('ms_purchase_order_items')
            ->whereIn('purchase_order_id', $poIds)
            ->get()
            ->groupBy('purchase_order_id');

        foreach ($purchaseInvoices as $inv) {
            $inv->items = ($itemsByPo[$inv->id] ?? collect())->values();
            $inv->item_count = $inv->items->count();
            $inv->total_units = (int) $inv->items->sum('qty');
        }

        // Summary KPIs
        $totalPOValue        = (float) $purchaseInvoices->sum('total_amount');
        $totalPODue          = (float) $purchaseInvoices->where('status', '!=', 'paid')->sum('balance_due');
        $totalInvoicesCount  = $purchaseInvoices->count();
        $totalUnitsPurchased = (int) $purchaseInvoices->sum('total_units');

        // Drop-downs for add forms and supplier payment modals
        $prefix = DB::getTablePrefix();
        $suppliers = DB::table('ms_suppliers')
            ->leftJoin('ms_supplier_credit_wallets', function ($j) use ($companyId) {
                $j->on('ms_suppliers.id', '=', 'ms_supplier_credit_wallets.supplier_id')
                  ->where('ms_supplier_credit_wallets.company_id', $companyId);
            })
            ->select('ms_suppliers.*', DB::raw("COALESCE({$prefix}ms_supplier_credit_wallets.credit_balance, 0) as credit_balance"))
            ->where('ms_suppliers.company_id', $companyId)
            ->get();

        $wallets = DB::table('ms_supplier_credit_wallets')
            ->join('ms_suppliers', 'ms_supplier_credit_wallets.supplier_id', '=', 'ms_suppliers.id')
            ->select('ms_supplier_credit_wallets.*', 'ms_suppliers.name as supplier_name')
            ->where('ms_supplier_credit_wallets.company_id', $companyId)
            ->get();
        $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $parts      = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();

        $knownBrands = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand')
            ->merge(
                DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->whereNotNull('brand')
                    ->where('brand', '!=', '')
                    ->where('brand', '!=', 'Universal')
                    ->distinct()
                    ->pluck('brand')
            )
            ->unique()
            ->sort()
            ->values();

        $knownModels = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->whereNotNull('model')
            ->where('model', '!=', '')
            ->distinct()
            ->pluck('model')
            ->merge(
                DB::table('ms_parts_inventory')
                    ->where('company_id', $companyId)
                    ->whereNotNull('compatible_model')
                    ->where('compatible_model', '!=', '')
                    ->where('compatible_model', '!=', 'Universal')
                    ->distinct()
                    ->pluck('compatible_model')
            )
            ->unique()
            ->sort()
            ->values();

        return view('mobileshop.purchase', compact(
            'niche', 'isAdmin', 'purchaseInvoices', 'purchaseOrders', 'newPhonePurchases', 'buybacks', 'batchRestocks',
            'canAddPhones', 'canAddSecondhand', 'canAddAccessories', 'canAddCovers',
            'totalPOValue', 'totalPODue', 'totalInvoicesCount', 'totalUnitsPurchased',
            'suppliers', 'wallets', 'categories', 'parts', 'knownBrands', 'knownModels'
        ));
    }

    /**
     * Dispatcher: Unified Purchase Store
     * Sniffs request payload attributes to route to the appropriate handler:
     * 1. 'second_hand' / 'customer_buyback_name' -> storeSecondHand (Customer device buyback)
     * 2. 'bulk_restock' / 'restock_qty'          -> bulkRestock (Batch inventory restock)
     * 3. 'accessory' / 'compatible_model'        -> storePart (Single accessory/part registration)
     * 4. Default                                 -> storeNewMobile (Supplier purchase order for new phone)
     */
    public function storePurchase(Request $request)
    {
        if ($request->input('type') === 'second_hand' || $request->has('customer_buyback_name')) {
            return app(StockController::class)->storeSecondHand($request);
        }
        if ($request->input('type') === 'bulk_restock' || $request->has('restock_qty')) {
            return app(AccessoriesController::class)->bulkRestock($request);
        }
        if ($request->input('type') === 'accessory' || $request->has('compatible_model')) {
            return app(AccessoriesController::class)->storePart($request);
        }
        return app(StockController::class)->storeNewMobile($request);
    }

    /**
     * Purchase Invoice & Inward Procurement Bill View
     */
    public function purchaseInvoice(Request $request, $id)
    {
        $companyId = $this->getCompanyId();
        $data = $this->resolvePurchaseOrderDetails($companyId, (int) $id);

        if ($data) {
            return view('mobileshop.purchase_invoice', $data);
        }

        $device = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('id', $id)->first();
        if ($device) {
            return redirect()->route('mobileshop.purchase')->with('info', "Buyback device: {$device->brand} {$device->model} (IMEI: {$device->imei_1})");
        }

        abort(404, 'Purchase record not found.');
    }

    /**
     * Download Purchase Invoice as Direct PDF
     */
    public function purchaseInvoicePdf(Request $request, $id)
    {
        $data = $this->resolvePurchaseOrderDetails($this->getCompanyId(), (int) $id);
        if (!$data) {
            abort(404, 'Purchase record not found.');
        }

        $pdf = Pdf::loadView('mobileshop.pdf.purchase_invoice', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download("PurchaseOrder-{$data['po']->po_number}.pdf");
    }

    /**
     * Purchase Registration Page — full page, multi-item bulk stock entry
     * Shows supplier running balances & advance (credit wallet) balances.
     */
    public function purchaseCreate()
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-purchase-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized access to purchase registration.');

        $companyId = $this->getCompanyId();

        $prefix = DB::getTablePrefix();
        // Suppliers with running outstanding (sum of unpaid PO balances) and advance credit
        $suppliers = DB::table('ms_suppliers')
            ->leftJoin('ms_purchase_orders', function ($j) use ($companyId) {
                $j->on('ms_suppliers.id', '=', 'ms_purchase_orders.supplier_id')
                  ->where('ms_purchase_orders.company_id', $companyId)
                  ->whereNotIn('ms_purchase_orders.status', ['paid', 'cancelled']);
            })
            ->leftJoin('ms_supplier_credit_wallets', function ($j) use ($companyId) {
                $j->on('ms_suppliers.id', '=', 'ms_supplier_credit_wallets.supplier_id')
                  ->where('ms_supplier_credit_wallets.company_id', $companyId);
            })
            ->where('ms_suppliers.company_id', $companyId)
            ->groupBy('ms_suppliers.id', 'ms_suppliers.name', 'ms_suppliers.phone', 'ms_suppliers.gstin')
            ->select(
                'ms_suppliers.id', 'ms_suppliers.name', 'ms_suppliers.phone', 'ms_suppliers.gstin',
                DB::raw("COALESCE(SUM({$prefix}ms_purchase_orders.balance_due), 0) as outstanding_balance"),
                DB::raw("COALESCE(MAX({$prefix}ms_supplier_credit_wallets.credit_balance), 0) as advance_balance")
            )
            ->orderBy('ms_suppliers.name')
            ->get();

        return view('mobileshop.purchase_create', compact('suppliers'));
    }

    /**
     * Store Bulk Multi-Item Purchase — creates one PO with many device lines,
     * carries unpaid balance to supplier ledger, applies advance credit wallet.
     */
    public function storeBulkPurchase(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-purchase-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'supplier_id'            => 'nullable|exists:ms_suppliers,id',
            'new_supplier_name'      => 'required_without:supplier_id|nullable|string|max:150',
            'new_supplier_phone'     => 'nullable|string|max:20',
            'supplier_invoice_no'    => 'nullable|string|max:100',
            'order_date'             => 'required|date',
            'bill_type'              => 'required|in:gst,non_gst',
            'bill_total'             => 'required|numeric|min:1',
            'amount_paid'            => 'required|numeric|min:0',
            'use_advance_credit'     => 'nullable|numeric|min:0',
            'payment_mode'           => 'required|in:cash,bank_transfer,cheque,upi',
            'items'                  => 'required|array|min:1',
            'items.*.brand'          => 'required|string|max:100',
            'items.*.model'          => 'required|string|max:100',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.unit_cost'      => 'required|numeric|min:0',
            'items.*.selling_price'  => 'required|numeric|min:1',
            'items.*.ram'            => 'nullable|string|max:50',
            'items.*.storage'        => 'nullable|string|max:50',
            'items.*.color'          => 'nullable|string|max:50',
            'items.*.imeis'          => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            // ── Resolve supplier (existing or create new) ──
            if ($request->filled('supplier_id')) {
                $supplierId = (int) $request->supplier_id;
            } else {
                $supplierId = DB::table('ms_suppliers')->insertGetId([
                    'company_id' => $companyId,
                    'name'       => trim($request->new_supplier_name),
                    'phone'      => $request->new_supplier_phone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $billTotal = round((float) $request->bill_total, 2);
            $amountPaid = min(round((float) $request->amount_paid, 2), $billTotal);
            $advanceUsed = 0.00;

            // ── Apply advance credit wallet if requested ──
            $wallet = DB::table('ms_supplier_credit_wallets')
                ->where('company_id', $companyId)->where('supplier_id', $supplierId)
                ->lockForUpdate()->first();

            if ($request->filled('use_advance_credit') && (float) $request->use_advance_credit > 0 && $wallet) {
                $advanceUsed = min((float) $request->use_advance_credit, (float) $wallet->credit_balance, $billTotal - $amountPaid);
                if ($advanceUsed > 0) {
                    $amountPaid += $advanceUsed;
                    $newCredit = (float) $wallet->credit_balance - $advanceUsed;
                    DB::table('ms_supplier_credit_wallets')->where('id', $wallet->id)->update([
                        'credit_balance' => $newCredit, 'updated_at' => now(),
                    ]);
                    DB::table('ms_supplier_credit_transactions')->insert([
                        'supplier_id' => $supplierId, 'txn_type' => 'credit_used',
                        'amount' => $advanceUsed, 'related_po_id' => null,
                        'balance_after' => $newCredit,
                        'remarks' => 'Advance credit applied on bulk purchase invoice',
                        'txn_date' => now(),
                    ]);
                }
            }

            $balanceDue = max(0.00, $billTotal - $amountPaid);
            $newStatus = ($balanceDue <= 0) ? 'paid' : (($amountPaid > 0) ? 'partially_paid' : 'received');

            // ── Create PO header ──
            $poNum = 'PO-BULK-' . date('Ymd') . '-' . rand(100, 999);
            $poId = DB::table('ms_purchase_orders')->insertGetId([
                'company_id'   => $companyId,
                'supplier_id'  => $supplierId,
                'po_number'    => $poNum,
                'order_date'   => $request->order_date,
                'tax_type'     => $request->bill_type === 'gst' ? 'intra_state' : 'none',
                'subtotal'     => $billTotal,
                'total_amount' => $billTotal,
                'amount_paid'  => $amountPaid,
                'balance_due'  => $balanceDue,
                'status'       => $newStatus,
                'created_by'   => auth()->id(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // ── Insert line items + bulk devices ──
            $deviceCount = 0;
            foreach ($request->items as $item) {
                $qty = (int) $item['qty'];
                $unitCost = (float) $item['unit_cost'];
                $lineTotal = round($unitCost * $qty, 2);
                $variantParts = [];
                if (!empty($item['ram']) && !empty($item['storage'])) $variantParts[] = $item['ram'] . '/' . $item['storage'];
                if (!empty($item['color'])) $variantParts[] = $item['color'];

                DB::table('ms_purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'brand'             => $item['brand'],
                    'model'             => $item['model'],
                    'variant'           => implode(' ', $variantParts) ?: 'Standard',
                    'hsn_code'          => '85171300',
                    'qty'               => $qty,
                    'qty_received'      => $qty,
                    'unit_cost'         => $unitCost,
                    'tax_rate'          => $request->bill_type === 'gst' ? 18.00 : 0,
                    'line_total'        => $lineTotal,
                ]);

                // Parse optional IMEI list (one per line / comma separated)
                $imeis = [];
                if (!empty($item['imeis'])) {
                    $imeis = preg_split('/[\n,]+/', trim($item['imeis']));
                    $imeis = array_values(array_filter(array_map('trim', $imeis)));
                }

                for ($i = 0; $i < $qty; $i++) {
                    $imei = !empty($imeis[$i]) ? substr($imeis[$i], 0, 20) : ('86' . str_pad($poId % 1000, 3, '0', STR_PAD_LEFT) . rand(1000000000, 9999999999));
                    DB::table('ms_mobile_devices')->insert([
                        'company_id'        => $companyId,
                        'purchase_order_id' => $poId,
                        'type'              => 'new',
                        'brand'             => $item['brand'],
                        'model'             => $item['model'],
                        'color'             => $item['color'] ?? 'Standard',
                        'ram'               => $item['ram'] ?? null,
                        'storage'           => $item['storage'] ?? null,
                        'imei_1'            => $imei,
                        'purchase_cost'     => $unitCost,
                        'selling_price'     => (float) $item['selling_price'],
                        'status'            => 'in_stock',
                        'condition_grade'   => 'brand_new',
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                    $deviceCount++;
                }
            }

            // ── Record cash payment row if paid now ──
            if ($amountPaid > 0) {
                DB::table('ms_supplier_payments')->insert([
                    'company_id'        => $companyId,
                    'supplier_id'       => $supplierId,
                    'purchase_order_id' => $poId,
                    'amount'            => $amountPaid,
                    'payment_date'      => now()->toDateString(),
                    'mode'              => $request->payment_mode,
                    'reference_no'      => $request->supplier_invoice_no,
                    'remarks'           => "Payment on bulk purchase {$poNum}" . ($advanceUsed > 0 ? " (advance credit used: ₹" . number_format($advanceUsed, 2) . ")" : ""),
                    'recorded_by'       => auth()->id(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            $msg = "Bulk purchase {$poNum} recorded — {$deviceCount} units added to stock.";
            if ($balanceDue > 0) {
                $msg .= " Balance due to supplier: ₹" . number_format($balanceDue, 2) . " (carried to supplier ledger).";
            }
            return redirect()->route('mobileshop.purchase', ['company_id' => $companyId])->with('success', $msg);
        });
    }

    /**
     * Supplier Purchase Orders Ledger & Inward Inventory
     */
    public function purchaseOrders()
    {
        return redirect()->route('mobileshop.purchase');
    }

    /**
     * Record Supplier Payment / Advance / Settlement
     */
    public function recordSupplierPayment(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-procurement') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'supplier_id' => 'required|exists:ms_suppliers,id',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,bank_transfer,cheque,upi',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $supplierId = $request->supplier_id;
            $paymentAmount = (float) $request->amount;
            $poId = $request->purchase_order_id;

            $wallet = DB::table('ms_supplier_credit_wallets')->where('company_id', $companyId)->where('supplier_id', $supplierId)->lockForUpdate()->first();
            if (!$wallet) {
                $walletId = DB::table('ms_supplier_credit_wallets')->insertGetId([
                    'company_id' => $companyId,
                    'supplier_id' => $supplierId,
                    'credit_balance' => 0.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $wallet = DB::table('ms_supplier_credit_wallets')->where('id', $walletId)->first();
            }

            if ($poId) {
                $po = DB::table('ms_purchase_orders')->where('company_id', $companyId)->where('id', $poId)->lockForUpdate()->first();
                if (!$po) {
                    return redirect()->back()->with('error', 'Purchase order not found.');
                }
                $applyToPO = min($paymentAmount, (float) $po->balance_due);
                $newPaid = (float) $po->amount_paid + $applyToPO;
                $newDue = (float) $po->balance_due - $applyToPO;
                $newStatus = ($newDue <= 0) ? 'paid' : 'partially_paid';

                DB::table('ms_purchase_orders')->where('id', $poId)->update([
                    'amount_paid' => $newPaid,
                    'balance_due' => $newDue,
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

                DB::table('ms_supplier_payments')->insert([
                    'company_id' => $companyId,
                    'supplier_id' => $supplierId,
                    'purchase_order_id' => $poId,
                    'amount' => $applyToPO,
                    'payment_date' => now()->toDateString(),
                    'mode' => $request->payment_mode,
                    'reference_no' => $request->reference_no,
                    'remarks' => $request->remarks,
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $excess = $paymentAmount - $applyToPO;
                if ($excess > 0) {
                    $newCredit = (float) $wallet->credit_balance + $excess;
                    DB::table('ms_supplier_credit_wallets')->where('supplier_id', $supplierId)->update([
                        'credit_balance' => $newCredit,
                        'updated_at' => now(),
                    ]);

                    DB::table('ms_supplier_credit_transactions')->insert([
                        'supplier_id' => $supplierId,
                        'txn_type' => 'credit_added',
                        'amount' => $excess,
                        'related_po_id' => $poId,
                        'balance_after' => $newCredit,
                        'remarks' => "Excess overpayment on PO #{$po->po_number} credited to wallet",
                        'txn_date' => now(),
                    ]);
                }
            } else {
                $newCredit = (float) $wallet->credit_balance + $paymentAmount;
                DB::table('ms_supplier_credit_wallets')->where('supplier_id', $supplierId)->update([
                    'credit_balance' => $newCredit,
                    'updated_at' => now(),
                ]);

                DB::table('ms_supplier_credit_transactions')->insert([
                    'supplier_id' => $supplierId,
                    'txn_type' => 'credit_added',
                    'amount' => $paymentAmount,
                    'related_po_id' => null,
                    'balance_after' => $newCredit,
                    'remarks' => "Direct advance payment to supplier wallet",
                    'txn_date' => now(),
                ]);
            }

            return redirect()->route('mobileshop.purchase')->with('success', 'Supplier payment successfully logged and ledger updated!');
        });
    }

    /**
     * Update Supplier details and/or adjust prepaid wallet balance
     */
    public function updateSupplier(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'supplier_id' => 'required|exists:ms_suppliers,id',
            'name' => 'required|string|max:191',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'gstin' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'credit_balance' => 'nullable|numeric|min:0',
            'adjustment_notes' => 'nullable|string|max:255',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            $supplier = DB::table('ms_suppliers')->where('company_id', $companyId)->where('id', $request->supplier_id)->first();
            if (!$supplier) {
                return redirect()->back()->with('error', 'Supplier not found.');
            }

            DB::table('ms_suppliers')->where('id', $supplier->id)->update([
                'name' => trim($request->name),
                'contact_person' => $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'gstin' => $request->gstin,
                'address' => $request->address,
                'updated_at' => now(),
            ]);

            $deltaMsg = "";
            if ($request->has('credit_balance') && $request->credit_balance !== null) {
                $wallet = DB::table('ms_supplier_credit_wallets')->where('company_id', $companyId)->where('supplier_id', $supplier->id)->lockForUpdate()->first();
                $newBal = round((float) $request->credit_balance, 2);
                $oldBal = $wallet ? (float) $wallet->credit_balance : 0.00;
                $delta = round($newBal - $oldBal, 2);

                if (!$wallet) {
                    DB::table('ms_supplier_credit_wallets')->insert([
                        'company_id' => $companyId,
                        'supplier_id' => $supplier->id,
                        'credit_balance' => $newBal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('ms_supplier_credit_wallets')->where('id', $wallet->id)->update([
                        'credit_balance' => $newBal,
                        'updated_at' => now(),
                    ]);
                }

                if (abs($delta) > 0.001) {
                    $txnType = $delta > 0 ? 'credit_added' : 'credit_applied';
                    DB::table('ms_supplier_credit_transactions')->insert([
                        'supplier_id' => $supplier->id,
                        'txn_type' => $txnType,
                        'amount' => abs($delta),
                        'balance_after' => $newBal,
                        'txn_date' => now(),
                        'remarks' => $request->adjustment_notes ?: ('Manual wallet adjustment by ' . auth()->user()->name),
                    ]);
                    $deltaMsg = " Prepaid wallet updated to ₹" . number_format($newBal, 2) . ".";
                }
            }

            return redirect()->back()->with('success', "Supplier '{$request->name}' ledger details updated.{$deltaMsg}");
        });
    }
}
