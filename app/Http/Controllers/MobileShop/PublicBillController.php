<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Common\MobileShopInvoiceResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PublicBillController extends BaseMobileShopController
{
    /**
     * Public responsive HTML bill viewer for customers
     */
    public function show($invoice_number)
    {
        $invoice_number = trim($invoice_number);

        // 1. Mobile Phone Sale
        $mobileSale = DB::table('ms_mobile_sales')
            ->where('invoice_number', $invoice_number)
            ->orWhere('invoice_number', strtoupper($invoice_number))
            ->first();
        if ($mobileSale) {
            $data = MobileShopInvoiceResolver::resolvePhoneSaleDetails((int)$mobileSale->company_id, (int)$mobileSale->id);
            return view('mobileshop.public.bill', array_merge($data, [
                'type' => 'phone',
                'invoice_number' => $invoice_number,
                'pdfUrl' => route('public.bill.pdf', ['invoice_number' => $invoice_number], false),
            ]));
        }

        // 2. Accessory Sale
        $accSale = DB::table('ms_accessory_sales')
            ->where('invoice_number', $invoice_number)
            ->orWhere('invoice_number', strtoupper($invoice_number))
            ->first();
        if ($accSale) {
            $data = MobileShopInvoiceResolver::resolveAccessorySaleDetails((int)$accSale->company_id, (int)$accSale->id);
            return view('mobileshop.public.bill', array_merge($data, [
                'type' => 'accessory',
                'invoice_number' => $invoice_number,
                'pdfUrl' => route('public.bill.pdf', ['invoice_number' => $invoice_number], false),
            ]));
        }

        abort(404, 'Invoice not found.');
    }

    /**
     * Direct PDF download / stream for customers (from WhatsApp link)
     */
    public function pdf($invoice_number)
    {
        $invoice_number = trim($invoice_number);

        // Ensure public folder and dompdf public path are always resolved safely
        if (!is_dir(base_path('public'))) {
            @mkdir(base_path('public'), 0755, true);
        }
        $resolvedPublic = is_dir(public_path()) ? public_path() : (is_dir(base_path('../public_html')) ? realpath(base_path('../public_html')) : (realpath(base_path('public')) ?: base_path()));
        config(['dompdf.public_path' => $resolvedPublic]);

        try {
            // 1. Mobile Phone Sale
            $mobileSale = DB::table('ms_mobile_sales')
                ->where('invoice_number', $invoice_number)
                ->orWhere('invoice_number', strtoupper($invoice_number))
                ->first();
            if ($mobileSale) {
                $data = MobileShopInvoiceResolver::resolvePhoneSaleDetails((int)$mobileSale->company_id, (int)$mobileSale->id);
                $pdf = Pdf::loadView('mobileshop.pdf.phone_invoice', $data);
                $pdf->setPaper('a4', 'portrait');

                $filename = "Invoice-{$invoice_number}.pdf";
                return (request()->has('stream') || request()->has('view'))
                    ? $pdf->stream($filename)
                    : $pdf->download($filename);
            }

            // 2. Accessory Sale
            $accSale = DB::table('ms_accessory_sales')
                ->where('invoice_number', $invoice_number)
                ->orWhere('invoice_number', strtoupper($invoice_number))
                ->first();
            if ($accSale) {
                $data = MobileShopInvoiceResolver::resolveAccessorySaleDetails((int)$accSale->company_id, (int)$accSale->id);
                $pdf = Pdf::loadView('mobileshop.pdf.accessory_invoice', $data);
                $pdf->setPaper('a4', 'portrait');

                $filename = "Invoice-{$invoice_number}.pdf";
                return (request()->has('stream') || request()->has('view'))
                    ? $pdf->stream($filename)
                    : $pdf->download($filename);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Public PDF generation error for invoice {$invoice_number}: " . $e->getMessage(), [
                'exception' => $e
            ]);
            // Graceful fallback to the public printable bill view
            return redirect()->route('public.bill.show', ['invoice_number' => $invoice_number, 'print' => 1])
                ->with('error', 'PDF generation is currently compiling. You can view and print directly.');
        }

        abort(404, 'Invoice not found.');
    }
}
