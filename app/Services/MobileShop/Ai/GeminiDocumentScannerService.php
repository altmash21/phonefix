<?php

namespace App\Services\MobileShop\Ai;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeminiDocumentScannerService
{
    /**
     * Resolve the active Gemini API key from config, env, or store settings.
     */
    public function getApiKey(): string
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            $key = env('GEMINI_API_KEY') ?: (env('GOOGLE_API_KEY') ?: env('GEMINI_KEY'));
        }
        if (empty($key) && function_exists('setting')) {
            $key = setting('mobileshop.gemini_api_key', '');
        }
        return (string) $key;
    }

    /**
     * Send payload to Gemini API with automatic model fallback (2.0-flash -> 1.5-flash).
     */
    public function callGemini(string $prompt, $file): array
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            return [
                'success' => false,
                'no_key'  => true,
                'message' => 'GEMINI_API_KEY is not configured in .env or settings.',
            ];
        }

        // Extract base64 and mime type
        if ($file instanceof UploadedFile) {
            $mimeType = $file->getMimeType();
            $base64Data = base64_encode(file_get_contents($file->getRealPath()));
        } elseif (is_string($file) && file_exists($file)) {
            $mimeType = mime_content_type($file);
            $base64Data = base64_encode(file_get_contents($file));
        } else {
            return [
                'success' => false,
                'message' => 'Invalid file input provided for OCR scanning.',
            ];
        }

        $primaryModel  = config('services.gemini.model', 'gemini-3.8-flash');
        $fallbackModel = config('services.gemini.fallback_model', 'gemini-3.6-flash');

        $modelsToTry = array_unique([$primaryModel, $fallbackModel, 'gemini-3.8-flash', 'gemini-3.6-flash', 'gemini-flash-latest']);
        $lastError = null;
        $rawResponse = null;

        foreach ($modelsToTry as $model) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data'      => $base64Data,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature'        => 0.1,
                    'response_mime_type' => 'application/json',
                ],
            ];

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 40);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $lastError = "Network error connecting to Gemini ({$model}): {$curlError}";
                continue;
            }

            if ($httpCode === 200) {
                $resData = json_decode($response, true);
                $rawText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                // Clean potential markdown wrap
                $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
                $rawText = preg_replace('/\s*```$/', '', $rawText);
                $decoded = json_decode($rawText, true);

                if (is_array($decoded)) {
                    return [
                        'success'    => true,
                        'data'       => $decoded,
                        'model_used' => $model,
                    ];
                }
                $lastError = "Could not parse JSON response from {$model}.";
            } else {
                $errBody = json_decode($response, true);
                $errMsg = $errBody['error']['message'] ?? "HTTP {$httpCode} error from {$model}";
                $lastError = $errMsg;
                Log::warning("Gemini OCR ({$model}) failed with code {$httpCode}: {$errMsg}");
            }
        }

        return [
            'success' => false,
            'message' => $lastError ?? 'All Gemini models failed to process the document.',
        ];
    }

    /**
     * Scan Retail Phone Bill / Customer Tax Invoice / EMI Slip (Sales POS & Sales Create).
     */
    public function scanRetailBill($file, ?int $companyId = null): array
    {
        $prompt = <<<PROMPT
You are an expert OCR engine for mobile phone retail stores in India.
Analyze this image of a Customer Phone Purchase Bill, Retail Tax Invoice, Cash Memo, Delivery Slip, or EMI Finance Downpayment Voucher.
Extract all relevant customer, phone, and financial information into a STRICT JSON object:

{
  "customer_name": "Full name of customer or null",
  "customer_phone": "10-digit Indian mobile number (e.g. 9876543210) or null",
  "customer_address": "Customer address/city or null",
  "customer_gstin": "Customer GSTIN or null",
  "brand": "Mobile brand name (e.g. Apple, Samsung, Vivo, Oppo, Xiaomi, Realme, OnePlus) or null",
  "model": "Model name with storage/color if visible (e.g. iPhone 15 128GB Black, Galaxy S24 5G) or null",
  "storage": "Storage capacity (e.g. 128GB, 256GB, 512GB) or null",
  "color": "Device color or null",
  "imei_1": "15-digit primary IMEI number or null",
  "imei_2": "15-digit secondary IMEI number or null",
  "selling_price": 0.00,
  "invoice_no": "Invoice number or null",
  "invoice_date": "YYYY-MM-DD date if mentioned, or null",
  "payment_mode": "cash / upi / card / emi / bank_transfer or null",
  "emi_provider": "Finance company name (e.g. Bajaj Finserv, TVS Credit, HDB, Home Credit, IDFC First, DMI Finance) or null",
  "emi_loan_no": "Loan account / agreement number or null",
  "emi_downpayment": 0.00
}

Rules:
1. Return ONLY the strict JSON object. No explanations or extra text.
2. Normalize customer_phone to exactly 10 digits without +91 or leading 0.
3. Clean IMEI numbers to exactly 15 digits (remove slashes, spaces, hyphens).
4. Numeric prices must be numbers (e.g. 54999.00), not strings.
5. If a field cannot be determined, use null or 0.00.
PROMPT;

        $apiResult = $this->callGemini($prompt, $file);

        // Fallback demo mode if no API key is configured
        if (!$apiResult['success'] && !empty($apiResult['no_key'])) {
            $sampleDevice = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('status', 'in_stock')
                ->where('type', 'new')
                ->first();

            $sampleProvider = DB::table('ms_emi_providers')
                ->where('company_id', $companyId)
                ->where('enabled', 1)
                ->first();

            $extracted = [
                'customer_name'    => 'Rohan Verma',
                'customer_phone'   => '9876543210',
                'customer_address' => 'Bandra West, Mumbai',
                'customer_gstin'   => null,
                'brand'            => $sampleDevice ? $sampleDevice->brand : 'Samsung',
                'model'            => $sampleDevice ? $sampleDevice->model : 'Galaxy S24 5G',
                'storage'          => $sampleDevice ? ($sampleDevice->storage ?? '128GB') : '128GB',
                'color'            => $sampleDevice ? ($sampleDevice->color ?? 'Onyx Black') : 'Onyx Black',
                'imei_1'           => $sampleDevice ? $sampleDevice->imei_1 : '354892019482035',
                'imei_2'           => $sampleDevice ? $sampleDevice->imei_2 : null,
                'selling_price'    => $sampleDevice ? (float)$sampleDevice->selling_price : 74999.00,
                'invoice_no'       => 'INV-' . date('Ymd') . '-042',
                'invoice_date'     => date('Y-m-d'),
                'payment_mode'     => 'emi',
                'emi_provider'     => $sampleProvider ? $sampleProvider->name : 'Bajaj Finserv Consumer Finance',
                'emi_loan_no'      => 'BJF-' . rand(100000, 999999),
                'emi_downpayment'  => 15000.00,
                'is_demo_mode'     => true,
            ];
            $extracted['imei'] = $extracted['imei_1'];
            $extracted['sale_price'] = $extracted['selling_price'];

            return array_merge($extracted, [
                'success'             => true,
                'data'                => $extracted,
                'matched_device'      => $sampleDevice,
                'matched_provider_id' => $sampleProvider ? $sampleProvider->id : null,
                'notice'              => 'Demo data parsed. Add GEMINI_API_KEY to your .env to scan live camera photos with Gemini Flash AI.',
            ]);
        }

        if (!$apiResult['success']) {
            return $apiResult;
        }

        $extracted = $apiResult['data'];

        // Normalize phone
        if (!empty($extracted['customer_phone'])) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$extracted['customer_phone']);
            if (strlen($cleanPhone) > 10) {
                $cleanPhone = substr($cleanPhone, -10);
            }
            $extracted['customer_phone'] = $cleanPhone;
        }

        // Backward compatibility aliases
        if (!empty($extracted['imei_1']) && empty($extracted['imei'])) {
            $extracted['imei'] = $extracted['imei_1'];
        }
        if (!empty($extracted['selling_price']) && empty($extracted['sale_price'])) {
            $extracted['sale_price'] = $extracted['selling_price'];
        }

        // Match device from in-stock inventory
        $matchedDevice = null;
        if (!empty($extracted['imei_1'])) {
            $cleanImei = preg_replace('/[^0-9]/', '', (string)$extracted['imei_1']);
            $matchedDevice = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('status', 'in_stock')
                ->where(function ($q) use ($cleanImei) {
                    $q->where('imei_1', $cleanImei)->orWhere('imei_2', $cleanImei);
                })
                ->first();
        }

        if (!$matchedDevice && !empty($extracted['model'])) {
            $matchedDevice = DB::table('ms_mobile_devices')
                ->where('company_id', $companyId)
                ->where('status', 'in_stock')
                ->where('model', 'LIKE', '%' . trim($extracted['model']) . '%')
                ->first();
        }

        // Match EMI provider
        $matchedProviderId = null;
        if (!empty($extracted['emi_provider'])) {
            $provName = trim($extracted['emi_provider']);
            $provider = DB::table('ms_emi_providers')
                ->where('company_id', $companyId)
                ->where('enabled', 1)
                ->where(function ($q) use ($provName) {
                    $q->where('name', 'LIKE', "%{$provName}%")
                      ->orWhere('code', 'LIKE', "%{$provName}%");
                })
                ->first();
            if ($provider) {
                $matchedProviderId = $provider->id;
            }
        }

        return array_merge($extracted, [
            'success'             => true,
            'data'                => $extracted,
            'matched_device'      => $matchedDevice,
            'matched_provider_id' => $matchedProviderId,
            'model_used'          => $apiResult['model_used'] ?? null,
        ]);
    }

    /**
     * Scan Vendor Distributor Purchase Invoice / Stock Intake Challan (Phones & Accessories).
     */
    public function scanPurchaseInvoice($file, ?int $companyId = null): array
    {
        $prompt = <<<PROMPT
You are an expert AI parser for wholesale distributor invoices and handwritten stock slips (kachha parchi / challans) for mobile phone and spare parts stores in India.
Analyze this image of a Vendor Purchase Invoice, Handwritten Paper Slip, Wholesale Delivery Challan, or Distributor Tax Invoice.
The document may contain smartphones, spare parts (display screen folders/combos, touch glass, charging connectors, batteries), accessories (chargers, cables, covers, tempered glass), or a mixture.

Slips and kachha bills commonly follow patterns like:
- Vendor / Shop Name at header (e.g., "Karan Azamgarh", "Gaffar Wholesale", "Rajdhani Mobile")
- Slip / Bill / Token number (e.g., "No. 46" -> supplier_invoice_no: "46")
- Date (e.g., "1/9/26" -> invoice_date: "2026-09-01")
- Line items with ditto marks (", ', or ,,) indicating repetition of the type above (e.g., repeat "HD+" or "Display Folder")
- Quantity x Rate (e.g., "30 x 620" means 30 pieces at ₹620 each)
- Underlined total at the bottom (e.g., "129300" -> bill_total: 129300.00)
- Display Folders / Combos are often abbreviated with "HD+", "OG", "Crown", "Diamond", "TFT", "OLED", "Folder", "Combo". "Rlm" stands for Realme.

Extract all supplier information and individual line items into a STRICT JSON object:

{
  "supplier_name": "Distributor / Supplier name or null",
  "supplier_phone": "Supplier 10-digit mobile / phone number or null",
  "supplier_gstin": "Supplier 15-character GSTIN number or null",
  "supplier_invoice_no": "Invoice number or Bill number or null",
  "invoice_date": "YYYY-MM-DD date or null",
  "bill_total": 0.00,
  "items": [
    {
      "type": "phone",
      "brand": "Brand name (e.g. Apple, Samsung, Vivo, OnePlus)",
      "model": "Model name (e.g. Galaxy A15 5G, iPhone 15)",
      "ram": "RAM if mentioned (e.g. 6GB, 8GB) or null",
      "storage": "Storage ROM if mentioned (e.g. 128GB, 256GB) or null",
      "color": "Color if mentioned or null",
      "qty": 1,
      "unit_cost": 0.00,
      "selling_price": 0.00,
      "imeis": ["15-digit IMEI 1", "15-digit IMEI 2 if present"]
    },
    {
      "type": "accessory",
      "category": "display_folder / tempered_glass / back_cover_case / front_glass / charging_pin / battery / ic_motherboard / back_panel / charger / cable / audio / general_accessory",
      "display_type": "OG / Normal (for display folders)",
      "brand": "Brand name (e.g. Realme, Samsung, Xiaomi, Vivo, Oppo, Apple, OnePlus, Infinix, Narzo, Universal)",
      "model": "Model name (e.g. C55 / C65, A53, Note 7, A57 New, Y20, C11, Narzo 30 Pro)",
      "name": "Full descriptive name (e.g. Realme C55 / C65 HD+ OG Display Folder, Samsung A53 HD+ Display Folder)",
      "qty": 10,
      "unit_cost": 0.00,
      "selling_price": 0.00,
      "barcode": "Barcode / EAN if printed, or null"
    }
  ]
}

Rules:
1. Return ONLY the strict JSON object. No markdown explanations or preamble.
2. For each phone row, extract all visible 15-digit IMEI numbers into the "imeis" array.
3. If selling_price is not printed on the wholesale slip:
   - For display folders/combos: estimate selling_price as unit_cost + ₹250 (e.g., cost 620 -> 870 or round to nearest ₹50).
   - For tempered glass and covers: unit_cost * 2.5 rounded.
   - For other items: unit_cost * 1.30 rounded.
4. If an item is a screen, folder, combo, LCD, OLED, or HD+, set category to "display_folder". If it mentions OG/Original, set display_type to "OG", else "Normal".
5. All numeric monetary fields must be numbers (e.g. 620.00).
6. If supplier_phone contains +91 or spaces, clean to digits.
PROMPT;

        $apiResult = $this->callGemini($prompt, $file);

        // Fallback demo mode if no API key is configured
        if (!$apiResult['success'] && !empty($apiResult['no_key'])) {
            $sampleSupplier = DB::table('ms_suppliers')
                ->where('company_id', $companyId)
                ->first();

            $extracted = [
                'supplier_name'       => $sampleSupplier ? $sampleSupplier->name : 'Samsung Televentures Pvt Ltd',
                'supplier_phone'      => $sampleSupplier ? $sampleSupplier->phone : '9820011223',
                'supplier_gstin'      => $sampleSupplier ? $sampleSupplier->gstin : '27AABCS1429B1Z8',
                'supplier_invoice_no' => 'INV-DIST-' . date('Ymd') . '-019',
                'invoice_date'        => date('Y-m-d'),
                'bill_total'          => 48500.00,
                'items'               => [
                    [
                        'type'          => 'phone',
                        'brand'         => 'Samsung',
                        'model'         => 'Galaxy A15 5G',
                        'ram'           => '6GB',
                        'storage'       => '128GB',
                        'color'         => 'Blue Black',
                        'qty'           => 2,
                        'unit_cost'     => 14200.00,
                        'selling_price' => 16499.00,
                        'imeis'         => ['359182019482011', '359182019482012'],
                    ],
                    [
                        'type'          => 'phone',
                        'brand'         => 'Vivo',
                        'model'         => 'Y200 5G',
                        'ram'           => '8GB',
                        'storage'       => '128GB',
                        'color'         => 'Desert Gold',
                        'qty'           => 1,
                        'unit_cost'     => 17500.00,
                        'selling_price' => 20999.00,
                        'imeis'         => ['864910294810293'],
                    ],
                    [
                        'type'          => 'accessory',
                        'category'      => 'Charger',
                        'name'          => 'Samsung 25W Super Fast Type-C Adapter Original',
                        'qty'           => 4,
                        'unit_cost'     => 650.00,
                        'selling_price' => 1299.00,
                        'barcode'       => '8806090123456',
                    ],
                ],
                'is_demo_mode'        => true,
            ];

            return array_merge($extracted, [
                'success'             => true,
                'data'                => $extracted,
                'matched_supplier_id' => $sampleSupplier ? $sampleSupplier->id : null,
                'notice'              => 'Demo invoice parsed. Live vendor bills will be processed with Gemini Flash AI.',
            ]);
        }

        if (!$apiResult['success']) {
            return $apiResult;
        }

        $extracted = $apiResult['data'];

        // Normalize aliases for supplier invoice number and total
        if (!empty($extracted['invoice_number']) && empty($extracted['supplier_invoice_no'])) {
            $extracted['supplier_invoice_no'] = $extracted['invoice_number'];
        } elseif (!empty($extracted['supplier_invoice_no']) && empty($extracted['invoice_number'])) {
            $extracted['invoice_number'] = $extracted['supplier_invoice_no'];
        }

        if (!empty($extracted['total_amount']) && empty($extracted['bill_total'])) {
            $extracted['bill_total'] = $extracted['total_amount'];
        } elseif (!empty($extracted['bill_total']) && empty($extracted['total_amount'])) {
            $extracted['total_amount'] = $extracted['bill_total'];
        }

        // Normalize items array
        if (!empty($extracted['items']) && is_array($extracted['items'])) {
            foreach ($extracted['items'] as &$it) {
                if (!isset($it['item_type'])) {
                    $it['item_type'] = (!empty($it['type']) && $it['type'] === 'accessory') ? 'accessory' : 'phone';
                }
                if ($it['item_type'] === 'accessory') {
                    if (!empty($it['brand']) && strtolower($it['brand']) === 'other') {
                        $it['brand'] = '';
                    }
                    if (!empty($it['model']) && strtolower($it['model']) === 'other') {
                        $it['model'] = '';
                    }
                    if (empty($it['brand']) && !empty($it['name'])) {
                        // Infer brand from name
                        $nm = strtolower($it['name']);
                        if (preg_match('/\b(realme|rlm|narzo)\b/', $nm)) $it['brand'] = 'Realme';
                        elseif (preg_match('/\b(samsung|galaxy)\b/', $nm)) $it['brand'] = 'Samsung';
                        elseif (preg_match('/\b(redmi|xiaomi|mi|poco)\b/', $nm)) $it['brand'] = 'Xiaomi';
                        elseif (preg_match('/\b(vivo|iqoo)\b/', $nm)) $it['brand'] = 'Vivo';
                        elseif (preg_match('/\b(oppo|reno)\b/', $nm)) $it['brand'] = 'Oppo';
                        elseif (preg_match('/\b(apple|iphone)\b/', $nm)) $it['brand'] = 'Apple';
                        elseif (preg_match('/\b(oneplus)\b/', $nm)) $it['brand'] = 'OnePlus';
                        elseif (preg_match('/\b(infinix)\b/', $nm)) $it['brand'] = 'Infinix';
                    }
                }
            }
            unset($it);
        }

        // Match existing supplier
        $matchedSupplierId = null;
        if (!empty($extracted['supplier_name'])) {
            $suppName = trim($extracted['supplier_name']);
            $supplier = DB::table('ms_suppliers')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($suppName) {
                    $q->where('name', 'LIKE', "%{$suppName}%");
                })
                ->first();

            if (!$supplier && !empty($extracted['supplier_phone'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$extracted['supplier_phone']);
                if ($cleanPhone) {
                    $supplier = DB::table('ms_suppliers')
                        ->where('company_id', $companyId)
                        ->where('phone', 'LIKE', "%{$cleanPhone}%")
                        ->first();
                }
            }

            if ($supplier) {
                $matchedSupplierId = $supplier->id;
            }
        }

        return array_merge($extracted, [
            'success'             => true,
            'data'                => $extracted,
            'matched_supplier_id' => $matchedSupplierId,
            'model_used'          => $apiResult['model_used'] ?? null,
        ]);
    }
}
