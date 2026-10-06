<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Stock\NewMobileStockService;
use App\Services\MobileShop\Stock\StockHistoryService;
use App\Services\MobileShop\Stock\StockDeletionService;
use App\Services\MobileShop\Common\MobileShopOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class StockController extends BaseMobileShopController
{
    protected NewMobileStockService $newMobileStockService;
    protected StockHistoryService $stockHistoryService;
    protected StockDeletionService $stockDeletionService;

    public function __construct(
        ?NewMobileStockService $newMobileStockService = null,
        ?StockHistoryService $stockHistoryService = null,
        ?StockDeletionService $stockDeletionService = null
    ) {
        $this->newMobileStockService = $newMobileStockService ?? new NewMobileStockService();
        $this->stockHistoryService = $stockHistoryService ?? new StockHistoryService();
        $this->stockDeletionService = $stockDeletionService ?? new StockDeletionService();
    }
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
                $newPhones  = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->whereNotIn('status', ['deleted', 'scraped'])->orderBy('id', 'desc')->get();
                break;

            case 'secondhand':
                $secondHandPhones = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->whereNotIn('status', ['deleted', 'scraped'])->orderBy('id', 'desc')->get();
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
                $newPhones        = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'new')->whereNotIn('status', ['deleted', 'scraped'])->orderBy('id', 'desc')->get();
                $secondHandPhones = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->whereNotIn('status', ['deleted', 'scraped'])->orderBy('id', 'desc')->get();
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

        $suppliers = DB::table('ms_suppliers')->where('company_id', $companyId)->orderBy('name')->get();
        $customers = DB::table('ms_customers')->where('company_id', $companyId)->orderBy('name')->get();

        return view('mobileshop.stock', compact(
            'niche',
            'newPhones', 'secondHandPhones', 'parts', 'repairTickets', 'categories', 'suppliers', 'customers',
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
     * Supports full editing of accessories/parts (including direct stock quantity adjustment
     * with ledger audit trail) and mobile devices.
     */
    public function updateStock(Request $request, $id)
    {
        $companyId = $this->getCompanyId();
        $itemType  = $request->input('item_type');

        // Check if item is a part in ms_parts_inventory
        $part = null;
        if ($itemType === 'part' || !$itemType) {
            $part = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('id', $id)->first();
        }

        if ($part || $itemType === 'part') {
            if (!$part) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Part not found in inventory.'], 404);
                }
                return redirect()->back()->with('error', 'Part not found in inventory.');
            }

            $oldStock = (int) $part->stock_qty;
            $newStock = $request->has('stock_qty') ? max(0, (int) $request->input('stock_qty')) : $oldStock;

            $updateData = [
                'updated_at' => now(),
            ];

            if ($request->filled('name')) {
                $updateData['name'] = trim($request->input('name'));
            }
            if ($request->filled('category')) {
                $updateData['category'] = trim($request->input('category'));
            }
            if ($request->has('brand')) {
                $updateData['brand'] = trim($request->input('brand', 'Universal')) ?: 'Universal';
            }
            if ($request->has('compatible_model')) {
                $updateData['compatible_model'] = trim($request->input('compatible_model', ''));
            }
            if ($request->filled('display_type')) {
                $rawDisp = strtolower(trim((string) $request->input('display_type')));
                $updateData['display_type'] = match($rawDisp) {
                    'og', 'original', 'original_oem'  => 'original_oem',
                    'hd+', 'hd_plus', 'hd plus', 'hd' => 'hd_plus',
                    'oled'                            => 'oled',
                    'in_cell', 'incell'               => 'in_cell',
                    'tft'                             => 'tft',
                    default                           => $rawDisp,
                };
            }
            if ($request->filled('unit_cost')) {
                $updateData['unit_cost'] = max(0, (float) $request->input('unit_cost'));
            }
            if ($request->filled('selling_price')) {
                $updateData['selling_price'] = max(0, (float) $request->input('selling_price'));
            }
            if ($request->has('stock_qty')) {
                $updateData['stock_qty'] = $newStock;
            }
            if ($request->filled('min_stock_alert')) {
                $updateData['min_stock_alert'] = max(0, (int) $request->input('min_stock_alert'));
            }
            if ($request->has('description')) {
                $updateData['description'] = $request->input('description');
            }
            if ($request->has('hsn_code')) {
                $updateData['hsn_code'] = trim($request->input('hsn_code')) ?: '85177090';
            }

            DB::table('ms_parts_inventory')->where('id', $id)->update($updateData);

            // If stock count changed, record audit ledger entries
            if ($request->has('stock_qty') && $newStock !== $oldStock) {
                $diff = $newStock - $oldStock;
                $diffType = $diff > 0 ? 'addition' : 'deduction';
                $reason = $request->input('adjustment_reason', 'Stock count manual edit');
                $userName = auth()->user()->name ?? 'Staff';

                // 1. ms_parts_inventory_history
                if (Schema::hasTable('ms_parts_inventory_history')) {
                    DB::table('ms_parts_inventory_history')->insert([
                        'part_id'       => $id,
                        'type'          => $diffType,
                        'quantity'      => abs($diff),
                        'balance_after' => $newStock,
                        'reference'     => "Manual adjustment: {$oldStock} -> {$newStock} by {$userName} ({$reason})",
                        'user_id'       => auth()->id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                // 2. ms_stock_audit_log
                if (Schema::hasTable('ms_stock_audit_log')) {
                    DB::table('ms_stock_audit_log')->insert([
                        'company_id'    => $companyId,
                        'item_type'     => 'part',
                        'item_id'       => $id,
                        'action'        => 'adjustment',
                        'quantity'      => abs($diff),
                        'balance_after' => $newStock,
                        'reason'        => "Stock count changed from {$oldStock} to {$newStock} by {$userName}. {$reason}",
                        'user_id'       => auth()->id(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }

            $freshPart = DB::table('ms_parts_inventory')->where('id', $id)->first();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Stock item '{$freshPart->name}' updated successfully.",
                    'item'    => $freshPart,
                ]);
            }

            return redirect()->back()->with('success', "Stock item '{$freshPart->name}' updated successfully.");
        }

        // Mobile Device Branch
        $device = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('id', $id)->first();
        if (!$device) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Mobile device not found.'], 404);
            }
            return redirect()->back()->with('error', 'Mobile device not found.');
        }

        $updateData = [
            'updated_at' => now(),
        ];

        if ($request->filled('brand')) $updateData['brand'] = $request->input('brand');
        if ($request->filled('model')) $updateData['model'] = $request->input('model');
        if ($request->has('color')) $updateData['color'] = $request->input('color');
        if ($request->has('ram')) $updateData['ram'] = $request->input('ram');
        if ($request->has('storage')) $updateData['storage'] = $request->input('storage');
        if ($request->filled('imei_1')) $updateData['imei_1'] = $request->input('imei_1');
        if ($request->has('imei_2')) $updateData['imei_2'] = $request->input('imei_2');
        if ($request->filled('purchase_cost')) $updateData['purchase_cost'] = (float) $request->input('purchase_cost');
        if ($request->filled('selling_price')) $updateData['selling_price'] = (float) $request->input('selling_price');
        if ($request->filled('status')) $updateData['status'] = $request->input('status');
        if ($request->filled('condition_grade')) $updateData['condition_grade'] = $request->input('condition_grade');
        if ($request->has('battery_health')) $updateData['battery_health'] = $request->input('battery_health');
        if ($request->has('checklist_notes')) $updateData['checklist_notes'] = $request->input('checklist_notes');
        if ($request->filled('min_stock_alert')) $updateData['min_stock_alert'] = (int) $request->input('min_stock_alert');

        $prefix = $device->type === 'second_hand' ? 'sh' : 'new';
        if ($request->hasFile('photo')) {
            $newPhoto = $this->uploadMobilePhoto($request, $prefix, 'photo');
            if ($newPhoto) {
                $updateData['photo_path'] = $newPhoto;
                if (empty($device->box_photo_path)) {
                    $updateData['box_photo_path'] = $newPhoto;
                }
            }
        }
        if ($request->hasFile('box_photo')) {
            $newBoxPhoto = $this->uploadMobilePhoto($request, $prefix . '_box', 'box_photo');
            if ($newBoxPhoto) {
                $updateData['box_photo_path'] = $newBoxPhoto;
            }
        }

        DB::table('ms_mobile_devices')->where('id', $id)->update($updateData);
        $freshDevice = DB::table('ms_mobile_devices')->where('id', $id)->first();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Device {$freshDevice->brand} {$freshDevice->model} updated successfully.",
                'item'    => $freshDevice,
            ]);
        }

        return redirect()->back()->with('success', "Device {$freshDevice->brand} {$freshDevice->model} updated.");
    }

    /**
     * Get single stock item details (JSON endpoint)
     */
    public function getStockDetails($id, Request $request)
    {
        $companyId = $this->getCompanyId();
        $type = $request->input('type', 'part');

        if ($type === 'part') {
            $part = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('id', $id)->first();
            if (!$part) {
                return response()->json(['success' => false, 'message' => 'Part not found in inventory'], 404);
            }
            return response()->json(['success' => true, 'item' => $part]);
        }

        $device = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('id', $id)->first();
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Mobile device not found'], 404);
        }
        return response()->json(['success' => true, 'item' => $device]);
    }

    /**
     * Brand New Mobiles Stock
     */
    public function newMobiles()
    {
        return redirect()->route('mobileshop.stock', ['tab' => 'new_phones']);
    }

    /**
     * Store Brand New Mobile into Inventory (with IMEI Uniqueness Check)
     */
    public function storeNewMobile(Request $request)
    {
        Gate::authorize('stock.create');

        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei_1' => 'required|string|max:30',
            'purchase_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:1',
        ]);

        $companyId = $this->getCompanyId();
        $photoPath = $this->uploadMobilePhoto($request, 'new', 'photo');
        $boxPhotoPath = $this->uploadMobilePhoto($request, 'new_box', 'box_photo');

        $result = $this->newMobileStockService->store($request, $companyId, $photoPath, $boxPhotoPath);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        return $this->safeRedirect($request, 'mobileshop.stock', ['tab' => 'new_phones'], 'success', "New mobile device {$result['brand']} {$result['model']} (IMEI: {$result['imei_1']}) added to stock (PO #{$result['po_number']})!");
    }

    /**
     * Panel 2: Second Hand Hub (Pre-Owned Stock & Buyback & Sales) - Redirects to Stock Hub
     */
    public function secondHand()
    {
        return redirect()->route('mobileshop.stock', ['tab' => 'second_hand']);
    }

    /**
     * Dedicated Second-Hand Mobile POS Sale Page
     */
    public function secondHandPos(Request $request)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('sell-mobileshop-secondhand') ||
            auth()->user()->can('read-mobileshop-secondhand') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('read-admin-panel') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized action.');

        $companyId = $this->getCompanyId();

        $devices = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'second_hand')
            ->where('status', 'in_stock')
            ->orderBy('brand', 'asc')
            ->orderBy('model', 'asc')
            ->get();

        $customers = DB::table('ms_customers')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();

        return view('mobileshop.second_hand_pos', compact('devices', 'customers'));
    }

    /**
     * Store Second Hand Buyback (Intake)
     */
    public function storeSecondHand(Request $request)
    {
        Gate::authorize('stock.create');

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
        $photoPath = $this->uploadMobilePhoto($request, 'sh', 'photo');
        $boxPhotoPath = $this->uploadMobilePhoto($request, 'sh_box', 'box_photo');
        $idProofPhotoPath = $this->uploadMobilePhoto($request, 'sh_kyc', 'id_proof_photo');

        return $this->safeRedirect($request, 'mobileshop.stock', [], 'info', "Second-hand device management is disabled.");
    }

    /**
     * Sell Second-Hand Mobile at Pre-Owned POS Counter
     */
    public function sellSecondHand(Request $request)
    {
        return redirect()->route('mobileshop.accessories.pos');
    }

    /**
     * Get Complete Stock History & Audit Trail for any stock item
     * (Spare Parts/Accessories, Brand New Phones, Pre-Owned Phones)
     */
    public function getStockHistory($type, $id)
    {
        abort_unless(auth()->check(), 401);

        $companyId = $this->getCompanyId();
        $result = $this->stockHistoryService->getHistory((string)$type, (int)$id, $companyId);

        $status = $result['status_code'] ?? 200;
        unset($result['status_code']);

        return response()->json($result, $status);
    }

    /**
     * Delete / Reduce Stock Item with Quantity & Audit Trail
     */
    public function deleteStockItem(Request $request)
    {
        Gate::authorize('stock.delete');

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
        $isOwner = $this->isOwner();
        if (!$isOwner) {
            $otpCode = $request->input('otp_code');
            if (!MobileShopOtpService::verifyOtp($companyId, 'delete_stock', $itemRef, $otpCode, false)) {
                return response()->json([
                    'success' => false,
                    'otp_required' => true,
                    'action' => 'delete_stock',
                    'item_reference' => $itemRef,
                    'message' => 'Store Owner OTP authorization is required to delete inventory stock.',
                ], 403);
            }
        }

        $result = $this->stockDeletionService->deleteItem($type, $id, $request->quantity, $fullReason, $companyId, $user);
        $status = $result['status_code'] ?? 200;
        unset($result['status_code']);

        return response()->json($result, $status);
    }

    /**
     * Request Store Owner OTP for Stock Deletion authorization
     */
    public function requestStockDeleteOtp(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:part,new_phone,second_hand',
            'item_id'   => 'required|integer',
        ]);

        $companyId = $this->getCompanyId();
        $itemRef   = "{$request->item_type}:{$request->item_id}";
        
        $result = MobileShopOtpService::generateOtp($companyId, 'delete_stock', $itemRef, auth()->id());

        if (!$result['sent']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Could not generate OTP.',
            ], 429);
        }

        return response()->json([
            'success'      => true,
            'message'      => "Authorization OTP dispatched to Store Owner ({$result['target_email']}). Valid for 5 minutes.",
            'target_email' => $result['target_email'],
            'expires_in'   => $result['expires_in'],
        ]);
    }
}
