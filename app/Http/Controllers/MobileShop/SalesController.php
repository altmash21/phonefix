<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesController extends BaseMobileShopController
{
    /**
     * Sales Hub — Niche-scoped sales list.
     */
    public function salesHub(Request $request)
    {
        $companyId = $this->getCompanyId();
        $niche     = $this->getUserNiche();
        $user      = auth()->user();

        // Determine what this role can create
        $canCreatePhones    = $user->can('create-sale-phones');
        $canCreateSecondhand = $user->can('create-sale-secondhand');
        $canCreateAccessories = $user->can('create-sale-accessories') || $user->hasRole('accessories-manager') || $user->hasRole('accessories-staff');
        $canCreateCovers    = $user->can('create-sale-covers') || $user->hasRole('accessories-manager') || $user->hasRole('accessories-staff');

        // Build niche-scoped sales list
        $mobileSales = collect();
        $accSales    = collect();

        switch ($niche) {
            case 'phones':
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1',
                             'ms_mobile_devices.storage', 'ms_mobile_devices.color', 'ms_mobile_devices.ram',
                             DB::raw("'new' as device_type"), DB::raw("'phone' as sale_niche"))
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_devices.type', 'new')
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->get();
                break;

            case 'secondhand':
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1',
                             'ms_mobile_devices.storage', 'ms_mobile_devices.color', 'ms_mobile_devices.ram',
                             DB::raw("'second_hand' as device_type"), DB::raw("'secondhand' as sale_niche"))
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_devices.type', 'second_hand')
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->get();
                break;

            case 'accessories':
                $accSales = DB::table('ms_accessory_sales')
                    ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
                    ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             DB::raw("'accessory' as sale_niche"))
                    ->where('ms_accessory_sales.company_id', $companyId)
                    ->where('ms_accessory_sales.status', '!=', 'voided')
                    ->orderBy('ms_accessory_sales.id', 'desc')->get();
                break;

            case 'covers':
                $coverCats = $this->coverCategories;
                $accSales = DB::table('ms_accessory_sales')
                    ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
                    ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             DB::raw("'cover' as sale_niche"))
                    ->where('ms_accessory_sales.company_id', $companyId)
                    ->where('ms_accessory_sales.status', '!=', 'voided')
                    ->whereExists(function($q) use ($coverCats) {
                        $q->from('ms_accessory_sale_items')
                          ->whereColumn('ms_accessory_sale_items.accessory_sale_id', 'ms_accessory_sales.id')
                          ->join('ms_parts_inventory', 'ms_accessory_sale_items.part_id', '=', 'ms_parts_inventory.id')
                          ->whereIn('ms_parts_inventory.category', $coverCats);
                    })
                    ->orderBy('ms_accessory_sales.id', 'desc')->get();
                break;

            default: // admin — all sales
                $mobileSales = DB::table('ms_mobile_sales')
                    ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
                    ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
                    ->select('ms_mobile_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone',
                             'ms_mobile_devices.brand', 'ms_mobile_devices.model', 'ms_mobile_devices.imei_1', 'ms_mobile_devices.type as device_type',
                             'ms_mobile_devices.storage', 'ms_mobile_devices.color', 'ms_mobile_devices.ram')
                    ->where('ms_mobile_sales.company_id', $companyId)
                    ->where('ms_mobile_sales.status', '!=', 'voided')
                    ->orderBy('ms_mobile_sales.id', 'desc')->get();
                $accSales = DB::table('ms_accessory_sales')
                    ->leftJoin('ms_customers', 'ms_accessory_sales.customer_id', '=', 'ms_customers.id')
                    ->select('ms_accessory_sales.*', 'ms_customers.name as customer_name', 'ms_customers.phone as customer_phone')
                    ->where('ms_accessory_sales.company_id', $companyId)
                    ->where('ms_accessory_sales.status', '!=', 'voided')
                    ->orderBy('ms_accessory_sales.id', 'desc')->get();
        }

        // Attach line items to accessory sales for interactive line-item partial return modal
        if ($accSales->isNotEmpty()) {
            $saleIds = $accSales->pluck('id')->toArray();
            $allItems = DB::table('ms_accessory_sale_items')
                ->where('company_id', $companyId)
                ->whereIn('accessory_sale_id', $saleIds)
                ->select('id', 'accessory_sale_id', 'part_id', 'part_name', 'quantity', 'unit_price', 'line_total')
                ->get()
                ->groupBy('accessory_sale_id');

            foreach ($accSales as $as) {
                $as->items = $allItems->get($as->id, collect())->values();
            }
        }

        // Aggregated KPIs for page header
        $todaySalesTotal = (float) ($niche === 'admin'
            ? DB::table('ms_mobile_sales')->where('company_id',$companyId)->whereDate('created_at',today())->where('status','!=','voided')->sum('total_amount')
              + DB::table('ms_accessory_sales')->where('company_id',$companyId)->whereDate('created_at',today())->where('status','!=','voided')->sum('total_amount')
            : ($mobileSales->sum('total_amount') + $accSales->sum('total_amount')));

        $monthSalesTotal = (float) ($niche === 'admin'
            ? DB::table('ms_mobile_sales')->where('company_id',$companyId)->whereMonth('created_at',now()->month)->whereYear('created_at',now()->year)->where('status','!=','voided')->sum('total_amount')
              + DB::table('ms_accessory_sales')->where('company_id',$companyId)->whereMonth('created_at',now()->month)->whereYear('created_at',now()->year)->where('status','!=','voided')->sum('total_amount')
            : ($mobileSales->sum('total_amount') + $accSales->sum('total_amount')));

        $salesCount = $mobileSales->count() + $accSales->count();

        // Stock availability for add-sale forms
        $availableNewPhones   = in_array($niche, ['admin','phones'])    ? DB::table('ms_mobile_devices')->where('company_id',$companyId)->where('type','new')->where('status','in_stock')->count() : 0;
        $availableSecondHand  = in_array($niche, ['admin','secondhand']) ? DB::table('ms_mobile_devices')->where('company_id',$companyId)->where('type','second_hand')->where('status','in_stock')->count() : 0;
        $availableParts       = in_array($niche, ['admin','accessories','covers']) ? DB::table('ms_parts_inventory')->where('company_id',$companyId)->where('stock_qty','>',0)->count() : 0;
        $customers            = DB::table('ms_customers')->where('company_id', $companyId)->get();
        $partsList            = DB::table('ms_parts_inventory')->where('company_id', $companyId)->where('stock_qty', '>', 0)->get();
        $categories           = DB::table('ms_part_categories')->where('company_id', $companyId)->orderBy('name', 'asc')->get();
        $secondHandPhones     = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('type', 'second_hand')->where('status', 'in_stock')->orderBy('brand')->orderBy('model')->get();

        return view('mobileshop.sales', compact(
            'niche', 'mobileSales', 'accSales',
            'canCreatePhones', 'canCreateSecondhand', 'canCreateAccessories', 'canCreateCovers',
            'todaySalesTotal', 'monthSalesTotal', 'salesCount',
            'availableNewPhones', 'availableSecondHand', 'availableParts', 'customers', 'partsList', 'categories', 'secondHandPhones'
        ));
    }

    /**
     * Panel 1: New Phones POS Screen — Redirects to Unified Sales Registration
     */
    public function pos()
    {
        return redirect()->route('mobileshop.sales.create');
    }

    /**
     * AI-Powered OCR Scan for EMI Slips, Delivery Challans & Invoices (Gemini 1.5 Flash)
     */
    public function scanEmiBill(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('read-mobileshop-pos') || auth()->user()->can('create-mobileshop-pos') || auth()->user()->can('create-sale-phones') || auth()->user()->can('read-mobileshop-sales') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized.');

        $request->validate([
            'bill_image' => 'required|file|mimes:jpeg,png,jpg,webp,pdf,heic|max:10240',
        ]);

        $apiKey = config('services.gemini.key') ?: (env('GEMINI_API_KEY') ?: (env('GOOGLE_API_KEY') ?: env('GEMINI_KEY')));
        if (empty($apiKey)) {
            $apiKey = setting('mobileshop.gemini_api_key', '');
        }

        if (empty($apiKey)) {
            $companyId = $this->getCompanyId();
            $sampleDevice = DB::table('ms_mobile_devices')->where('company_id', $companyId)->where('status', 'in_stock')->where('type', 'new')->first();
            $sampleProvider = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('enabled', 1)->first();

            $extracted = [
                'customer_name'   => 'Rohan Verma',
                'customer_phone'  => '9876543210',
                'brand'           => $sampleDevice ? $sampleDevice->brand : 'Samsung',
                'model'           => $sampleDevice ? $sampleDevice->model : 'Galaxy S24 5G',
                'imei'            => $sampleDevice ? $sampleDevice->imei_1 : '354892019482035',
                'emi_provider'    => $sampleProvider ? $sampleProvider->name : 'Bajaj Finserv Consumer Finance',
                'emi_loan_no'     => 'BJF-' . rand(100000, 999999),
                'emi_downpayment' => 15000.00,
                'sale_price'      => $sampleDevice ? (float)$sampleDevice->selling_price : 74999.00,
                'is_demo_mode'    => true,
            ];

            return response()->json([
                'success' => true,
                'data' => $extracted,
                'matched_device' => $sampleDevice,
                'matched_provider_id' => $sampleProvider ? $sampleProvider->id : null,
                'notice' => 'Demo slip parsed! To use live Gemini 1.5 Flash AI OCR for camera photos, add GEMINI_API_KEY in your .env.'
            ]);
        }

        $file = $request->file('bill_image');
        $mimeType = $file->getMimeType();
        $base64Data = base64_encode(file_get_contents($file->getRealPath()));

        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

        $prompt = <<<PROMPT
You are an expert OCR and retail document parser for a mobile phone store in India.
Analyze this image of an EMI Finance Slip, Delivery Challan, Retail Invoice, or Down Payment receipt.
Extract the following information accurately into a strict JSON object:

{
  "customer_name": "Full name of customer or null",
  "customer_phone": "10-digit mobile number or null",
  "customer_address": "Customer address / city if mentioned, or null",
  "customer_gstin": "GSTIN number if mentioned, or null",
  "brand": "Mobile brand (e.g. Apple, Samsung, Vivo, Oppo, Xiaomi, Realme, OnePlus) or null",
  "model": "Model name / variant (e.g. iPhone 15 128GB, Vivo V29, Galaxy S24) or null",
  "imei": "15-digit IMEI 1 number if visible, or null",
  "emi_provider": "Finance company name (e.g. Bajaj Finserv, TVS Credit, HDB Financial, Home Credit, IDFC First, DMI Finance) or null",
  "emi_loan_no": "Loan account number / agreement / reference ID or null",
  "emi_downpayment": 0.00,
  "sale_price": 0.00
}

Rules:
1. Return ONLY the JSON object. Do not include markdown code fences (```json), commentary, or extra text.
2. Clean phone numbers to standard 10-digit Indian mobile format if possible.
3. Clean IMEI numbers to 15 digits (remove spaces/slashes).
4. If a field cannot be determined, use null or 0.00 for numbers.
PROMPT;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'response_mime_type' => 'application/json'
            ]
        ];

        try {
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                return response()->json([
                    'success' => false,
                    'message' => 'Network error connecting to Gemini API: ' . $curlError
                ], 500);
            }

            if ($httpCode !== 200) {
                $errBody = json_decode($response, true);
                $errMsg = $errBody['error']['message'] ?? "Gemini API returned error HTTP {$httpCode}";
                return response()->json([
                    'success' => false,
                    'message' => $errMsg
                ], 500);
            }

            $resData = json_decode($response, true);
            $rawText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
            $rawText = preg_replace('/\s*```$/', '', $rawText);
            $extracted = json_decode($rawText, true);

            if (!is_array($extracted)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to parse AI output from document. Please try a clearer picture.',
                    'raw' => $rawText
                ], 422);
            }

            $companyId = $this->getCompanyId();
            $matchedDevice = null;
            if (!empty($extracted['imei'])) {
                $cleanImei = preg_replace('/[^0-9]/', '', (string)$extracted['imei']);
                $matchedDevice = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)
                    ->where('status', 'in_stock')
                    ->where('type', 'new')
                    ->where(function($q) use ($cleanImei) {
                        $q->where('imei_1', $cleanImei)->orWhere('imei_2', $cleanImei);
                    })
                    ->first();
            }

            if (!$matchedDevice && !empty($extracted['model'])) {
                $matchedDevice = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)
                    ->where('status', 'in_stock')
                    ->where('type', 'new')
                    ->where('model', 'LIKE', '%' . trim($extracted['model']) . '%')
                    ->first();
            }

            $matchedProviderId = null;
            if (!empty($extracted['emi_provider'])) {
                $provName = trim($extracted['emi_provider']);
                $provider = DB::table('ms_emi_providers')
                    ->where('company_id', $companyId)
                    ->where('enabled', 1)
                    ->where(function($q) use ($provName) {
                        $q->where('name', 'LIKE', "%{$provName}%")
                          ->orWhere('code', 'LIKE', "%{$provName}%");
                    })
                    ->first();
                if ($provider) {
                    $matchedProviderId = $provider->id;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $extracted,
                'matched_device' => $matchedDevice,
                'matched_provider_id' => $matchedProviderId
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during OCR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Brand New Mobile Sale
     */
    public function processSale(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-mobileshop-pos') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin')), 403, 'Unauthorized action.');
        
        $request->validate([
            'customer_phone' => 'required|string|min:7|max:20',
            'customer_name' => 'required|string|min:2|max:100',
            'device_id' => 'required|exists:ms_mobile_devices,id',
            'sale_price' => 'required|numeric|min:1',
            'amount_paid' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,upi,card,bank_transfer,emi,credit_udhari,split',
        ]);

        $companyId = $this->getCompanyId();

        // Idempotency Check
        if ($request->filled('idempotency_key')) {
            $existing = DB::table('ms_mobile_sales')
                ->where('company_id', $companyId)
                ->where('idempotency_key', $request->idempotency_key)
                ->first();
            if ($existing) {
                return redirect()->route('mobileshop.invoice', ['id' => $existing->id])
                    ->with('success', "Sale invoice #{$existing->invoice_number} already processed (Idempotent response).");
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
                ->where('type', 'new')
                ->lockForUpdate()
                ->first();

            if (!$device) {
                return redirect()->back()->with('error', 'Selected new mobile device is no longer in stock!');
            }

            $salePrice = (float) $request->sale_price;
            $amountPaid = (float) $request->amount_paid;
            $reqTaxRate = (float) ($request->tax_rate ?? 18.00);
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst');
            $billType = $isGst ? 'gst' : 'non_gst';
            
            // Tax Calculation
            $gst = $this->calculateGst($salePrice, $reqTaxRate, $billType, $storeState, $customer->state_code ?? null);
            $taxRate = $gst['taxRate'];
            $cgst = $gst['cgst'];
            $sgst = $gst['sgst'];
            $igst = $gst['igst'];
            $isStateMatch = $gst['isStateMatch'];

            $udhariAmount = max(0.00, $salePrice - $amountPaid);

            $emiProviderId = $request->payment_mode === 'emi' ? $request->emi_provider_id : null;
            $emiProcessingFee = 0.00;
            if ($request->payment_mode === 'emi' && $emiProviderId) {
                $emiDownpayment = (float) ($request->emi_downpayment ?? 0.00);
                $emiFinanced = max(0.00, $salePrice - $emiDownpayment);

                $feeType = $request->input('emi_fee_type', 'flat');
                $feeVal  = (float) ($request->input('emi_fee_value', $request->input('emi_processing_fee', 0)));
                $emiProcessingFee = $feeType === 'percent' ? round(($emiFinanced * $feeVal) / 100, 2) : round($feeVal, 2);

                $provider = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('id', $emiProviderId)->lockForUpdate()->first();
                if (!$provider || $provider->advance_balance < $emiFinanced) {
                    $avail = $provider ? $provider->advance_balance : 0;
                    return redirect()->back()->with('error', "Finance pool for provider is insufficient! Available: ₹{$avail}, Required: ₹{$emiFinanced}");
                }

                DB::table('ms_emi_providers')->where('id', $emiProviderId)->decrement('advance_balance', $emiFinanced);
                DB::table('ms_emi_provider_transactions')->insert([
                    'emi_provider_id' => $emiProviderId,
                    'type' => 'sale_deduction',
                    'amount' => $emiFinanced,
                    'balance_after' => $provider->advance_balance - $emiFinanced,
                    'reference_no' => $request->emi_loan_no,
                    'notes' => "Financing for {$device->brand} {$device->model} (IMEI: {$device->imei_1})",
                    'created_at' => now(),
                ]);
            }

            // Atomic Sequential Invoice Number
            $invoiceNumber = $this->getNextInvoiceNumber($companyId, 'INV');

            // Create Sale Record
            $saleId = DB::table('ms_mobile_sales')->insertGetId([
                'company_id'          => $companyId,
                'idempotency_key'     => $request->idempotency_key ?? Str::uuid()->toString(),
                'customer_id'         => $customer->id,
                'invoice_number'      => $invoiceNumber,
                'bill_type'           => $billType,
                'device_id'           => $device->id,
                'sale_price'          => $salePrice,
                'tax_rate'            => $taxRate,
                'tax_type'            => $isStateMatch ? 'intra_state' : 'inter_state',
                'cgst_amount'         => $cgst,
                'sgst_amount'         => $sgst,
                'igst_amount'         => $igst,
                'total_amount'        => $salePrice,
                'amount_paid'         => $amountPaid,
                'udhari_amount'       => $udhariAmount,
                'payment_mode'        => $request->payment_mode,
                'emi_provider_id'     => $emiProviderId,
                'emi_loan_no'         => $request->emi_loan_no,
                'emi_downpayment'     => $request->emi_downpayment ?? 0.00,
                'emi_financed_amount' => $emiFinanced ?? 0.00,
                'emi_processing_fee'  => $emiProcessingFee,
                'emi_monthly_amount'  => $request->emi_monthly_amount ?? 0.00,
                'emi_tenure_months'   => (int) ($request->emi_tenure_months ?: 12),
                'sold_by'             => auth()->id(),
                'status'              => 'completed',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // Update Device Status
            DB::table('ms_mobile_devices')->where('id', $device->id)->update([
                'status' => 'sold',
                'selling_price' => $salePrice,
                'updated_at' => now(),
            ]);

            // Gifts Attachment & Stock Decrement from Accessories & Parts Inventory
            if ($request->has('gift_ids') && is_array($request->gift_ids)) {
                foreach ($request->gift_ids as $giftPartId) {
                    $part = DB::table('ms_parts_inventory')
                        ->where('company_id', $companyId)
                        ->where('id', $giftPartId)
                        ->lockForUpdate()
                        ->first();

                    if ($part && $part->stock_qty >= 1) {
                        $newBalance = $part->stock_qty - 1;
                        DB::table('ms_parts_inventory')->where('id', $part->id)->update([
                            'stock_qty' => $newBalance,
                            'updated_at' => now(),
                        ]);

                        DB::table('ms_sale_gifts')->insert([
                            'sale_id' => $saleId,
                            'gift_id' => $part->id,
                            'qty' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        DB::table('ms_parts_inventory_history')->insert([
                            'part_id' => $part->id,
                            'type' => 'deduction',
                            'quantity' => 1,
                            'balance_after' => $newBalance,
                            'reference' => "Promotional Gift on Phone Sale #{$invoiceNumber}",
                            'user_id' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Customer Khata Update if Udhari
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
                    'remarks' => "Udhari on Phone Sale Invoice #{$invoiceNumber}",
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return redirect()->route('mobileshop.invoice', ['id' => $saleId])->with('success', "Sale #{$invoiceNumber} successfully recorded!");
        });
    }

    /**
     * Invoice View (Dual-Format: 80mm Thermal & A4 Tax Invoice)
     */
    public function invoice($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-pos') || 
            auth()->user()->can('read-mobileshop-secondhand') ||
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('sales-staff') ||
            auth()->user()->hasRole('secondhand-staff')
        ), 403, 'Unauthorized access to invoices.');

        $data = $this->resolvePhoneSaleDetails($this->getCompanyId(), (int) $id);
        return view('mobileshop.invoice', $data);
    }

    /**
     * Download Mobile Sales Invoice as Direct PDF
     */
    public function invoicePdf($id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('read-mobileshop-pos') || 
            auth()->user()->can('read-mobileshop-secondhand') ||
            auth()->user()->hasRole('admin') || 
            auth()->user()->hasRole('store-admin') || 
            auth()->user()->hasRole('sales-staff') ||
            auth()->user()->hasRole('secondhand-staff')
        ), 403, 'Unauthorized access to invoice PDF.');

        $data = $this->resolvePhoneSaleDetails($this->getCompanyId(), (int) $id);
        $pdf = Pdf::loadView('mobileshop.pdf.phone_invoice', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download("Invoice-{$data['sale']->invoice_number}.pdf");
    }

    /**
     * Void / Cancel a Mobile Device Sale (Sales Return)
     */
    public function voidMobileSale(Request $request, $id)
    {
        abort_unless(auth()->check() && (
            auth()->user()->can('void-mobileshop-sales') ||
            auth()->user()->can('read-mobileshop-sales') ||
            auth()->user()->can('create-mobileshop-pos') ||
            auth()->user()->can('create-sale-phones') ||
            auth()->user()->hasRole('admin') ||
            auth()->user()->hasRole('store-admin')
        ), 403, 'Unauthorized to process sales return.');

        $request->validate([
            'void_reason' => 'nullable|string',
            'return_reason_code' => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();
        $shouldRestock = $request->has('should_restock') ? (bool) $request->should_restock : true;
        $reasonLabel   = $request->input('reason_label') ?: ($request->input('return_reason_code') ?: 'Customer Return');
        $customDetails = $request->input('void_reason') ? " — {$request->input('void_reason')}" : "";
        $auditReason   = "{$reasonLabel}{$customDetails}";

        // OTP Security Gate: Only owner can void without OTP
        $itemRef = "sale:{$id}";
        if (!$this->isOwner()) {
            $otpCode = $request->input('otp_code');
            if (!$this->verifyOtp($companyId, 'void_sale', $itemRef, $otpCode)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'otp_required' => true,
                        'action' => 'void_sale',
                        'item_reference' => $itemRef,
                        'message' => 'Store Owner OTP authorization is required to void sales invoices.',
                    ], 403);
                }
                return redirect()->back()->with('error', 'Store Owner OTP authorization is required to void sales invoices.');
            }
        }

        return DB::transaction(function () use ($request, $id, $companyId, $shouldRestock, $auditReason) {
            $sale = DB::table('ms_mobile_sales')
                ->where('company_id', $companyId)
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$sale || $sale->status === 'voided') {
                return redirect()->back()->with('error', 'Sale is already voided or cannot be found.');
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
                        'user_id' => auth()->id(),
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
                        'recorded_by' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }
            }

            // Mark Sale as Voided
            DB::table('ms_mobile_sales')->where('id', $id)->update([
                'status' => 'voided',
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'void_reason' => $auditReason . ($shouldRestock ? " [Restocked to Inventory]" : " [Marked Defective]"),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', "Sale #{$sale->invoice_number} has been Returned and device processed.");
        });
    }

    /**
     * Dispatcher: Unified Sale Store
     */
    public function storeSale(Request $request)
    {
        if ($request->has('items') || $request->input('sale_type') === 'accessory') {
            return app(AccessoriesController::class)->sellAccessory($request);
        }
        if ($request->input('device_type') === 'second_hand' || $request->input('type') === 'second_hand') {
            return app(StockController::class)->sellSecondHand($request);
        }
        return $this->processSale($request);
    }

    /**
     * Sale Registration Page — full page, multi-device selection
     */
    public function saleCreate()
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-sale-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized access to sale registration.');

        $companyId = $this->getCompanyId();
        $inStockDevices = DB::table('ms_mobile_devices')
            ->where('company_id', $companyId)
            ->where('type', 'new')
            ->where('status', 'in_stock')
            ->orderBy('brand')->orderBy('model')
            ->get();
        $customers = DB::table('ms_customers')->where('company_id', $companyId)->orderBy('name')->get();
        $emiProviders = DB::table('ms_emi_providers')->where('company_id', $companyId)->where('enabled', 1)->get();
        $giftInventory = DB::table('ms_parts_inventory')
            ->where('company_id', $companyId)
            ->where('stock_qty', '>', 0)
            ->select('id', 'name', 'category', 'brand', 'unit_cost', 'selling_price', 'stock_qty')
            ->orderBy('name')
            ->get();

        return view('mobileshop.sales_create', compact('inStockDevices', 'customers', 'emiProviders', 'giftInventory'));
    }

    /**
     * Store Multi-Device Sale — creates one sale invoice per device for the
     * same customer; unpaid total flows to customer Khata (udhari).
     */
    public function storeMultiSale(Request $request)
    {
        abort_unless(auth()->check() && (auth()->user()->can('create-sale-phones') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('store-admin') || auth()->user()->hasRole('sales-staff')), 403, 'Unauthorized action.');

        $request->validate([
            'customer_phone'   => 'required|string|min:7|max:20',
            'customer_name'    => 'required|string|min:2|max:100',
            'device_ids'       => 'required|array|min:1',
            'device_ids.*'     => 'exists:ms_mobile_devices,id',
            'sale_prices'      => 'required|array',
            'amount_paid'      => 'required|numeric|min:0',
            'payment_mode'     => 'required|in:cash,online,upi,card,bank_transfer,emi,credit_udhari,split',
        ]);

        $companyId = $this->getCompanyId();

        return DB::transaction(function () use ($request, $companyId) {
            // Find or create customer
            $customer = $this->findOrCreateCustomer($companyId, $request);

            $billTotal = 0.00;
            $totalDeviceCost = 0.00;
            $devices = [];
            foreach ($request->device_ids as $idx => $devId) {
                $device = DB::table('ms_mobile_devices')
                    ->where('company_id', $companyId)->where('id', $devId)
                    ->where('status', 'in_stock')->where('type', 'new')
                    ->lockForUpdate()->first();
                if (!$device) {
                    return redirect()->back()->with('error', "Device #{$devId} is no longer in stock. Sale cancelled — please retry.");
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

            $storeState = $this->getStoreStateCode();
            $reqTaxRate = (float) ($request->tax_rate ?? 18.00);
            $isGst = $request->boolean('is_gst') || ($request->bill_type === 'gst') || (!$request->has('bill_type') && !$request->has('is_gst'));
            $billType = $isGst ? 'gst' : 'non_gst';

            $firstInvoice = null;
            $lastInvoice = null;
            foreach ($devices as $d) {
                $invoiceNumber = $this->getNextInvoiceNumber($companyId, 'INV');
                $salePrice = $d['price'];
                $gst = $this->calculateGst($salePrice, $reqTaxRate, $billType, $storeState, $customer->state_code ?? null);

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

            return redirect()->route('mobileshop.invoice', ['id' => $firstInvoice])
                ->with('success', count($devices) . " sale invoice recorded for {$customer->name}. Total: ₹" . number_format($billTotal, 2)
                    . ($isEmi ? " (Financed: ₹" . number_format($emiFinanced, 2) . ", DP: ₹" . number_format($amountPaid, 2) . ")" : "")
                    . ($udhariTotal > 0 ? ", Added to Khata: ₹" . number_format($udhariTotal, 2) : "")
                    . ($giftName ? ", Free Gift: {$giftName}" : "") . ".");
        });
    }
}
