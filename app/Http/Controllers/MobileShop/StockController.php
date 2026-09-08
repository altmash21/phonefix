<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StockController extends BaseMobileShopController
{
    /**
     * Stock Hub — Niche-scoped inventory view.
     * Each role sees ONLY their own niche's stock.
     */
    public function stockHub(Request $request)
    {
        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $user      = auth()->user();

        $canManagePhones      = $user->can('manage-stock-phones');
        $canManageSecondhand  = $user->can('manage-stock-secondhand');
        $canManageAccessories = $user->can('manage-stock-accessories');
        $canManageCovers      = $user->can('manage-stock-covers');
        $canManageRepairs     = $user->can('manage-stock-repairs');

        $newPhones        = collect();
        $secondHandPhones = collect();
        $parts            = collect();
        $repairTickets    = collect();
        $categories       = collect();

        switch ($niche) {
            case 'phones':
                $newPhones  = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->where('status', '!=', 'deleted')->orderBy('id', 'desc')->get();
                break;

            case 'secondhand':
                $secondHandPhones = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->where('status', '!=', 'deleted')->orderBy('id', 'desc')->get();
                break;

            case 'accessories':
                $parts      = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
                $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
                break;

            case 'covers':
                $coverCats  = $this->coverCategories;
                $parts      = DB::table('ms_parts_inventory')->where('company_id', $companyId)->whereIn('category', $coverCats)->orderBy('name', 'asc')->get();
                $categories = DB::table('ms_part_categories')->where('company_id', $companyId)->whereIn('slug', $coverCats)->orderBy('name', 'asc')->get();
                break;

            case 'repairs':
                $repairTickets = DB::table('ms_repair_tickets')
                    ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
                    ->select('ms_repair_tickets.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
                    ->where('ms_repair_tickets.company_id', $companyId)
                    ->orderBy('ms_repair_tickets.id', 'desc')->get();
                break;

            default: // admin — all stock
                $newPhones        = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->where('status', '!=', 'deleted')->orderBy('id', 'desc')->get();
                $secondHandPhones = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->where('status', '!=', 'deleted')->orderBy('id', 'desc')->get();
                $parts            = DB::table('ms_parts_inventory')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
                $categories       = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
                $repairTickets    = DB::table('ms_repair_tickets')
                    ->join('ms_customers', 'ms_repair_tickets.customer_id', '=', 'ms_customers.id')
                    ->select('ms_repair_tickets.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
                    ->where('ms_repair_tickets.company_id', $companyId)
                    ->orderBy('ms_repair_tickets.id', 'desc')->get();
        }

        // Summary stats for this niche
        $totalNewPhonesInStock    = $newPhones->where('status', 'in_stock')->count();
        $totalSecondHandInStock   = $secondHandPhones->where('status', 'in_stock')->count();
        $totalPartsInStock        = (int) $parts->sum('stock_qty');
        $lowStockCount            = $parts->filter(fn($p) => (int) $p->stock_qty <= (int) ($p->min_stock_alert ?? 3))->count();
        $valuationCost = (float) $newPhones->where('status','in_stock')->sum('purchase_cost')
                       + (float) $secondHandPhones->where('status','in_stock')->sum('purchase_cost')
                       + (float) $parts->sum(fn($p) => ($p->unit_cost ?? 0) * ($p->stock_qty ?? 0));
        $valuationRetail = (float) $newPhones->where('status','in_stock')->sum('selling_price')
                         + (float) $secondHandPhones->where('status','in_stock')->sum('selling_price')
                         + (float) $parts->sum(fn($p) => ($p->selling_price ?? 0) * ($p->stock_qty ?? 0));

        return view('mobileshop.stock', compact(
            'niche',
            'newPhones', 'secondHandPhones', 'parts', 'repairTickets', 'categories',
            'canManagePhones', 'canManageSecondhand', 'canManageAccessories', 'canManageCovers', 'canManageRepairs',
            'totalNewPhonesInStock', 'totalSecondHandInStock', 'totalPartsInStock',
            'lowStockCount', 'valuationCost', 'valuationRetail'
        ));
    }

    /**
     * Dispatcher: Unified Stock Add
     * Sniffs request payload attributes to route to the appropriate handler:
     * 1. 'part' / 'compatible_model' / 'category' -> storePart (Accessory / Spare part)
     * 2. 'second_hand' stock type                 -> storeSecondHand (Buyback stock)
     * 3. Default                                  -> storeNewMobile (New mobile phone stock)
     */
    public function storeStock(Request $request)
    {
        if ($request->input('stock_type') === 'part' || $request->has('compatible_model') || $request->input('category')) {
            return app(AccessoriesController::class)->storePart($request);
        }
        if ($request->input('stock_type') === 'second_hand' || $request->input('type') === 'second_hand') {
            return $this->storeSecondHand($request);
        }
        return $this->storeNewMobile($request);
    }

    /**
     * Unified Stock Update
     */
    public function updateStock(Request $request, $id)
    {
        $companyId = $this->getCompanyId();
        if ($request->input('item_type') === 'part') {
            $part = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('id', $id)->first();
            if (!$part) {
                return redirect()->back()->with('error', 'Part not found.');
            }
            DB::table('ms_parts_inventory')->where('id', $id)->update([
                'selling_price'   => $request->filled('selling_price') ? (float) $request->selling_price : $part->selling_price,
                'min_stock_alert' => $request->filled('min_stock_alert') ? (int) $request->min_stock_alert : $part->min_stock_alert,
                'updated_at'      => now(),
            ]);
            return redirect()->back()->with('success', "Stock item '{$part->name}' updated.");
        }

        $device = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('id', $id)->first();
        if (!$device) {
            return redirect()->back()->with('error', 'Mobile device not found.');
        }
        DB::table('ms_mobile_devices')->where('id', $id)->update([
            'selling_price' => $request->filled('selling_price') ? (float) $request->selling_price : $device->selling_price,
            'status'        => $request->filled('status') ? $request->status : $device->status,
            'updated_at'    => now(),
        ]);
        return redirect()->back()->with('success', "Device {$device->brand} {$device->model} updated.");
    }

    /**
     * Brand New Mobiles Stock
     */
    public function newMobiles()
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-new') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized access to new mobiles inventory.');

        $companyId = $this->getCompanyId();
        $mobiles = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'new')
            ->orderBy('id', 'desc')
            ->get();

        return view('mobileshop.new_mobiles', compact('mobiles'));
    }

    /**
     * Store Brand New Mobile into Inventory (with IMEI Uniqueness Check)
     */
    public function storeNewMobile(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-pos') || auth()->user()->can('read-mobileshop-new') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei_1' => 'required|string|max:30',
            'purchase_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:1',
        ]);

        $companyId = $this->getCompanyId();

        // IMEI Uniqueness check within company
        $exists = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('imei_1', $request->imei_1)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "A mobile device with IMEI {$request->imei_1} is already registered in inventory!");
        }

        $supplier = DB::table('ms_suppliers')->where('company_id', $companyId)->first();
        $supplierId = $supplier ? $supplier->id : 1;
        $poNum = 'PO-PHONES-' . date('Ymd') . '-' . rand(100, 999);
        $phoneCost = (float) $request->purchase_cost;

        $photoPath = $this->uploadMobilePhoto($request, 'new');

        $poId = DB::table('ms_purchase_orders')->insertGetId([
            'company_id'   => $companyId,
            'supplier_id'  => $supplierId,
            'po_number'    => $poNum,
            'order_date'   => now()->toDateString(),
            'tax_type'     => 'intra_state',
            'subtotal'     => $phoneCost,
            'total_amount' => $phoneCost,
            'amount_paid'  => $phoneCost,
            'balance_due'  => 0,
            'status'       => 'received',
            'created_by'   => auth()->id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('ms_purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'brand'             => $request->brand,
            'model'             => $request->model,
            'variant'           => ($request->ram ? $request->ram . '/' . $request->storage : '') . ($request->color ? ' (' . $request->color . ')' : '') . " [IMEI: {$request->imei_1}]",
            'hsn_code'          => '85171300',
            'qty'               => 1,
            'qty_received'      => 1,
            'unit_cost'         => $phoneCost,
            'tax_rate'          => 18.00,
            'line_total'        => $phoneCost,
        ]);

        DB::table('ms_mobile_devices')->insert([
            'company_id' => $companyId,
            'purchase_order_id' => $poId,
            'type' => 'new',
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color ?? 'Standard',
            'ram' => $request->ram,
            'storage' => $request->storage,
            'imei_1' => $request->imei_1,
            'imei_2' => $request->imei_2,
            'purchase_cost' => $phoneCost,
            'selling_price' => (float) $request->selling_price,
            'photo_path' => $photoPath,
            'box_photo_path' => $photoPath,
            'status' => 'in_stock',
            'condition_grade' => 'brand_new',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', "New mobile device {$request->brand} {$request->model} (IMEI: {$request->imei_1}) added to stock (PO #{$poNum})!");
        }

        return redirect()->route('mobileshop.purchase')->with('success', "New mobile device {$request->brand} {$request->model} (IMEI: {$request->imei_1}) added to stock (PO #{$poNum})!");
    }

    /**
     * Panel 2: Second Hand Hub (Pre-Owned Stock & Buyback & Sales)
     */
    public function secondHand()
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-secondhand') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized access to second-hand hub.');

        $companyId = $this->getCompanyId();
        $mobiles = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'second_hand')
            ->orderBy('id', 'desc')
            ->get();

        $availablePhones = $mobiles->where('status', 'in_stock');
        $customers = DB::table('ms_customers')->where('company_id', $companyId)->get();

        return view('mobileshop.second_hand', compact('mobiles', 'availablePhones', 'customers'));
    }

    /**
     * Store Second Hand Buyback (Intake)
     */
    public function storeSecondHand(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-secondhand') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');

        $request->validate([
            'brand' => 'required',
            'model' => 'required',
            'imei_1' => 'required',
            'purchase_cost' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'customer_buyback_name' => 'required',
            'customer_buyback_phone' => 'required',
        ]);

        $companyId = $this->getCompanyId();

        // Check uniqueness within company
        $exists = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('imei_1', $request->imei_1)
            ->exists();
        if ($exists) {
            return redirect()->back()->with('error', "A mobile device with IMEI {$request->imei_1} is already registered in inventory!");
        }

        $suppName = 'Walk-in Buyback - ' . $request->customer_buyback_name;
        $supp = DB::table('ms_suppliers')->where('company_id', $companyId)->where('name', $suppName)->first();
        if (!$supp) {
            $suppId = DB::table('ms_suppliers')->insertGetId([
                'company_id' => $companyId,
                'name'       => $suppName,
                'phone'      => $request->customer_buyback_phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $suppId = $supp->id;
        }

        $buybackCost = (float) $request->purchase_cost;

        $photoPath = $this->uploadMobilePhoto($request, 'sh');

        $bbNum = 'BUYBACK-' . date('Ymd') . '-' . rand(100, 999);
        $poId = DB::table('ms_purchase_orders')->insertGetId([
            'company_id'   => $companyId,
            'supplier_id'  => $suppId,
            'po_number'    => $bbNum,
            'order_date'   => now()->toDateString(),
            'tax_type'     => 'intra_state',
            'subtotal'     => $buybackCost,
            'total_amount' => $buybackCost,
            'amount_paid'  => $buybackCost,
            'balance_due'  => 0,
            'status'       => 'received',
            'created_by'   => auth()->id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('ms_purchase_order_items')->insert([
            'purchase_order_id' => $poId,
            'brand'             => $request->brand,
            'model'             => $request->model,
            'variant'           => "Pre-Owned [Grade: " . str_replace('_', ' ', $request->condition_grade ?? 'A') . "] IMEI: {$request->imei_1}",
            'hsn_code'          => '85171300',
            'qty'               => 1,
            'qty_received'      => 1,
            'unit_cost'         => $buybackCost,
            'tax_rate'          => 0,
            'line_total'        => $buybackCost,
        ]);

        DB::table('ms_mobile_devices')->insert([
            'company_id' => $companyId,
            'purchase_order_id' => $poId,
            'type' => 'second_hand',
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color,
            'ram' => $request->ram,
            'storage' => $request->storage,
            'imei_1' => $request->imei_1,
            'imei_2' => $request->imei_2,
            'purchase_cost' => $buybackCost,
            'selling_price' => (float) $request->selling_price,
            'photo_path' => $photoPath,
            'box_photo_path' => $photoPath,
            'condition_grade' => $request->condition_grade ?? 'like_new_A_plus',
            'battery_health' => $request->battery_health,
            'customer_buyback_name' => $request->customer_buyback_name,
            'customer_buyback_phone' => $request->customer_buyback_phone,
            'customer_buyback_id_proof' => $request->customer_buyback_id_proof,
            'checklist_notes' => $request->checklist_notes,
            'status' => 'in_stock',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', "Second-hand mobile buyback registered (Invoice #{$bbNum})!");
        }

        return redirect()->route('mobileshop.purchase')->with('success', "Second-hand mobile buyback registered (Invoice #{$bbNum})!");
    }

    /**
     * Sell Second-Hand Mobile at Pre-Owned POS Counter
     */
    public function sellSecondHand(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('sell-mobileshop-secondhand') || auth()->user()->can('create-mobileshop-pos') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');

        $request->validate([
            'customer_phone' => 'required',
            'customer_name' => 'required',
            'device_id' => 'required|exists:ms_mobile_devices,id',
            'sale_price' => 'required|numeric|min:1',
            'amount_paid' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,upi,card,credit_udhari,split',
        ]);

        $companyId = $this->getCompanyId();

        // Idempotency check
        if ($request->filled('idempotency_key')) {
            $existing = DB::table('ms_mobile_sales')
                ->where('company_id', $companyId)
                ->where('idempotency_key', $request->idempotency_key)
                ->first();
            if ($existing) {
                return redirect()->route('mobileshop.invoice', ['id' => $existing->id])
                    ->with('success', "Pre-owned sale invoice #{$existing->invoice_number} already processed.");
            }
        }

        return DB::transaction(function () use ($request, $companyId) {
            $storeState = $this->getStoreStateCode();
            $customer = $this->findOrCreateCustomer($companyId, $request);

            // Lock device row
            $device = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('id', $request->device_id)
                ->where('status', 'in_stock')
                ->where('type', 'second_hand')
                ->lockForUpdate()
                ->first();

            if (!$device) {
                return redirect()->back()->with('error', 'Selected second-hand device is no longer available!');
            }

            $salePrice = (float) $request->sale_price;
            $amountPaid = (float) $request->amount_paid;
            $reqTaxRate = (float) ($request->tax_rate ?? 18.00);
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst');
            $billType = $isGst ? 'gst' : 'non_gst';

            $gst = $this->calculateGst($salePrice, $reqTaxRate, $billType, $storeState, $customer->state_code ?? null);
            $taxRate = $gst['taxRate'];
            $cgst = $gst['cgst'];
            $sgst = $gst['sgst'];
            $igst = $gst['igst'];
            $isStateMatch = $gst['isStateMatch'];

            $udhariAmount = max(0.00, $salePrice - $amountPaid);

            // Atomic Sequential Invoice Number for Second-Hand
            $invoiceNumber = $this->getNextInvoiceNumber($companyId, 'SH');

            $saleId = DB::table('ms_mobile_sales')->insertGetId([
                'company_id' => $companyId,
                'idempotency_key' => $request->idempotency_key ?? Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'invoice_number' => $invoiceNumber,
                'bill_type' => $billType,
                'device_id' => $device->id,
                'sale_price' => $salePrice,
                'tax_rate' => $taxRate,
                'tax_type' => $isStateMatch ? 'intra_state' : 'inter_state',
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_amount' => $salePrice,
                'amount_paid' => $amountPaid,
                'udhari_amount' => $udhariAmount,
                'payment_mode' => $request->payment_mode,
                'sold_by' => auth()->id(),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update Device Status
            DB::table('ms_mobile_devices')->where('id', $device->id)->update([
                'status' => 'sold',
                'selling_price' => $salePrice,
                'updated_at' => now(),
            ]);

            // Update Customer Khata if Udhari
            if ($udhariAmount > 0) {
                DB::table('ms_customers')->where('id', $customer->id)->increment('udhari_balance', $udhariAmount);
                $newBal = $customer->udhari_balance + $udhariAmount;
                DB::table('ms_customer_khata_transactions')->insert([
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'type' => 'udhari_sale',
                    'sale_id' => $saleId,
                    'amount' => $udhariAmount,
                    'balance_after' => $newBal,
                    'remarks' => "Udhari on Pre-Owned Phone Sale #{$invoiceNumber}",
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return redirect()->route('mobileshop.invoice', ['id' => $saleId])->with('success', "Pre-owned sale #{$invoiceNumber} successfully recorded!");
        });
    }

    /**
     * Get Complete Stock History & Audit Trail for any stock item
     * (Spare Parts/Accessories, Brand New Phones, Pre-Owned Phones)
     */
    public function getStockHistory($type, $id)
    {
        abort_unless(auth()->check(), 401);

        $companyId = $this->getCompanyId();
        $history = [];
        $itemInfo = [];

        if ($type === 'part') {
            $part = DB::table('ms_parts_inventory')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->first();

            if (!$part) {
                return response()->json(['success' => false, 'message' => 'Part not found in inventory'], 404);
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
                return response()->json(['success' => false, 'message' => 'Mobile device not found'], 404);
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

        return response()->json([
            'success' => true,
            'item' => $itemInfo,
            'history' => $history,
        ]);
    }

    /**
     * Delete / Reduce Stock Item with Quantity & Audit Trail
     */
    public function deleteStockItem(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $request->validate([
            'item_type' => 'required|in:part,new_phone,second_hand',
            'item_id'   => 'required|integer',
            'quantity'  => 'nullable|integer|min:1',
            'reason'    => 'required|string|max:200',
            'notes'     => 'nullable|string|max:500',
        ]);

        $companyId = $this->getCompanyId();
        $user      = auth()->user();
        $type      = $request->item_type;
        $id        = (int) $request->item_id;
        $reason    = trim($request->reason);
        $notes     = trim($request->notes ?? '');
        $fullReason = $reason . ($notes ? " — {$notes}" : "");

        // OTP Security Gate: Only owner (store-admin/admin) can delete without OTP
        $itemRef = "{$type}:{$id}";
        if (!$this->isOwner()) {
            $otpCode = $request->input('otp_code');
            if (!$this->verifyOtp($companyId, 'delete_stock', $itemRef, $otpCode)) {
                return response()->json([
                    'success' => false,
                    'otp_required' => true,
                    'action' => 'delete_stock',
                    'item_reference' => $itemRef,
                    'message' => 'Store Owner OTP authorization is required to delete inventory stock.',
                ], 403);
            }
        }

        if ($type === 'part') {
            $part = DB::table('ms_parts_inventory')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->first();

            if (!$part) {
                return response()->json(['success' => false, 'message' => 'Part item not found in stock.'], 404);
            }

            $currentStock = (int) $part->stock_qty;
            $qtyToDelete  = (int) ($request->quantity ?? 1);

            if ($currentStock > 0 && $qtyToDelete > $currentStock) {
                return response()->json(['success' => false, 'message' => "Cannot delete {$qtyToDelete} units. Only {$currentStock} units available in stock."], 422);
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

            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$qtyToDelete} unit(s) of '{$part->name}'. Remaining stock: {$newStock} units. Action logged by {$user->name}.",
                'new_stock' => $newStock,
                'item_id' => $id,
                'item_type' => 'part',
            ]);

        } else {
            // Phone: new_phone or second_hand
            $device = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->first();

            if (!$device) {
                return response()->json(['success' => false, 'message' => 'Mobile device not found in inventory.'], 404);
            }

            if ($device->status === 'sold') {
                return response()->json(['success' => false, 'message' => 'Cannot delete a device that has already been sold. Please process a Sales Return if needed.'], 422);
            }

            DB::transaction(function () use ($id, $device, $type, $fullReason, $user, $companyId) {
                DB::table('ms_mobile_devices')->where('id', $id)->update([
                    'status'     => 'deleted',
                    'updated_at' => now(),
                ]);

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
            return response()->json([
                'success' => true,
                'message' => "Device '{$devName}' removed from stock. Action logged by {$user->name}.",
                'status'  => 'deleted',
                'item_id' => $id,
                'item_type' => $type,
            ]);
        }
    }
}
