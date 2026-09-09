<?php

namespace App\Services\MobileShop\Sales;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmiBillScannerService
{
    /**
     * Scan an EMI Finance Slip or Retail Invoice image using Gemini Flash OCR.
     */
    public function scan(Request $request, int $companyId): array
    {
        $apiKey = config('services.gemini.key') ?: (env('GEMINI_API_KEY') ?: (env('GOOGLE_API_KEY') ?: env('GEMINI_KEY')));
        if (empty($apiKey)) {
            $apiKey = setting('mobileshop.gemini_api_key', '');
        }

        if (empty($apiKey)) {
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

            return [
                'success' => true,
                'data' => $extracted,
                'matched_device' => $sampleDevice,
                'matched_provider_id' => $sampleProvider ? $sampleProvider->id : null,
                'notice' => 'Demo slip parsed! To use live Gemini 1.5 Flash AI OCR for camera photos, add GEMINI_API_KEY in your .env.'
            ];
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
            return [
                'success' => false,
                'message' => 'Network error connecting to Gemini API: ' . $curlError,
                'status_code' => 500
            ];
        }

        if ($httpCode !== 200) {
            $errBody = json_decode($response, true);
            $errMsg = $errBody['error']['message'] ?? "Gemini API returned error HTTP {$httpCode}";
            return [
                'success' => false,
                'message' => $errMsg,
                'status_code' => 500
            ];
        }

        $resData = json_decode($response, true);
        $rawText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

        $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
        $rawText = preg_replace('/\s*```$/', '', $rawText);
        $extracted = json_decode($rawText, true);

        if (!is_array($extracted)) {
            return [
                'success' => false,
                'message' => 'Failed to parse AI output from document. Please try a clearer picture.',
                'raw' => $rawText,
                'status_code' => 422
            ];
        }

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

        return [
            'success' => true,
            'data' => $extracted,
            'matched_device' => $matchedDevice,
            'matched_provider_id' => $matchedProviderId
        ];
    }
}
