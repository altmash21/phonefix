<?php

namespace App\Services\MobileShop\Sales;

use App\Services\MobileShop\Ai\GeminiDocumentScannerService;
use Illuminate\Http\Request;

class EmiBillScannerService
{
    protected GeminiDocumentScannerService $scanner;

    public function __construct(?GeminiDocumentScannerService $scanner = null)
    {
        $this->scanner = $scanner ?? new GeminiDocumentScannerService();
    }

    /**
     * Scan an EMI Finance Slip, Retail Bill, or Tax Invoice image using Gemini AI OCR.
     */
    public function scan(Request $request, int $companyId): array
    {
        $file = $request->file('bill_image');
        if (!$file && $request->hasFile('invoice_image')) {
            $file = $request->file('invoice_image');
        }

        if (!$file) {
            return [
                'success' => false,
                'message' => 'No image file uploaded for scanning.',
            ];
        }

        $result = $this->scanner->scanRetailBill($file, $companyId);

        if ($result['success']) {
            // Ensure backwards compatibility aliases
            $imei = $result['data']['imei'] ?? ($result['data']['imei_1'] ?? ($result['imei_1'] ?? null));
            $salePrice = $result['data']['sale_price'] ?? ($result['data']['selling_price'] ?? ($result['selling_price'] ?? null));

            if (!empty($result['data'])) {
                $result['data']['imei'] = $imei;
                $result['data']['sale_price'] = $salePrice;
            }
            $result['imei'] = $imei;
            $result['sale_price'] = $salePrice;
        }

        return $result;
    }

    /**
     * Programmatic scan helper for test scripts and console commands.
     */
    public function scanBillImage($file, ?int $companyId = null): array
    {
        $req = new Request();
        $req->files->set('bill_image', $file);
        return $this->scan($req, $companyId ?? 0);
    }
}
