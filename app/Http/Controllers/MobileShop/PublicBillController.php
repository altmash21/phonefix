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
        // 1. Mobile Phone Sale
        $mobileSale = DB::table('ms_mobile_sales')->where('invoice_number', $invoice_number)->first();
        if ($mobileSale) {
            $data = MobileShopInvoiceResolver::resolvePhoneSaleDetails((int)$mobileSale->company_id, (int)$mobileSale->id);
            return view('mobileshop.public.bill', array_merge($data, [
                'type' => 'phone',
                'invoice_number' => $invoice_number,
                'pdfUrl' => route('public.bill.pdf', ['invoice_number' => $invoice_number]),
            ]));
        }

        // 2. Accessory Sale
        $accSale = DB::table('ms_accessory_sales')->where('invoice_number', $invoice_number)->first();
        if ($accSale) {
            $data = MobileShopInvoiceResolver::resolveAccessorySaleDetails((int)$accSale->company_id, (int)$accSale->id);
            return view('mobileshop.public.bill', array_merge($data, [
                'type' => 'accessory',
                'invoice_number' => $invoice_number,
                'pdfUrl' => route('public.bill.pdf', ['invoice_number' => $invoice_number]),
            ]));
        }

        abort(404, 'Invoice not found.');
    }

    /**
     * Direct PDF download / stream for customers (from WhatsApp link)
     */
    public function pdf($invoice_number)
    {
        // 1. Mobile Phone Sale
        $mobileSale = DB::table('ms_mobile_sales')->where('invoice_number', $invoice_number)->first();
        if ($mobileSale) {
            $data = MobileShopInvoiceResolver::resolvePhoneSaleDetails((int)$mobileSale->company_id, (int)$mobileSale->id);
            $pdf = Pdf::loadView('mobileshop.pdf.phone_invoice', $data);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream("Invoice-{$invoice_number}.pdf");
        }

        // 2. Accessory Sale
        $accSale = DB::table('ms_accessory_sales')->where('invoice_number', $invoice_number)->first();
        if ($accSale) {
            $data = MobileShopInvoiceResolver::resolveAccessorySaleDetails((int)$accSale->company_id, (int)$accSale->id);
            $pdf = Pdf::loadView('mobileshop.pdf.accessory_invoice', $data);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream("Invoice-{$invoice_number}.pdf");
        }

        abort(404, 'Invoice not found.');
    }
}
