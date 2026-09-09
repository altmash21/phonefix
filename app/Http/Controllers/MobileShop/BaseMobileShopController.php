<?php

namespace App\Http\Controllers\MobileShop;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Carbon\Carbon;

abstract class BaseMobileShopController extends Controller
{
    /**
     * Cover-staff category filter — back covers + tempered glass only.
     */
    protected $coverCategories = ['back_cover', 'back_panel', 'tempered_glass'];

    /**
     * Resolve current company context (Fail-Closed Multi-Tenancy)
     */
    protected function getCompanyId(): int
    {
        $companyId = company_id() ?? session('company_id') ?? (auth()->check() ? (auth()->user()->companies()->first()?->id ?? auth()->user()->company_id) : null);
        if (!$companyId) {
            abort(403, 'Multi-tenant context error: No active company found in session.');
        }
        if (auth()->check() && !auth()->user()->companies()->where('companies.id', $companyId)->exists()) {
            abort(403, 'Unauthorized company access attempt.');
        }
        return (int) $companyId;
    }

    /**
     * Check if the authenticated user has Store Owner / Admin privileges.
     * Role 'store-admin' or 'admin' grants owner rights (bypasses OTP).
     */
    protected function isOwner(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        $user = auth()->user();
        return $user->hasRole('store-admin') || $user->hasRole('admin');
    }

    /**
     * Safe internal redirect to prevent Open Redirect vulnerabilities.
     * Only allows local relative URLs or absolute URLs matching current application host.
     */
    protected function safeRedirect(Request $request, string $fallbackRoute, array $routeParams = [], string $statusKey = 'success', string $statusMsg = '')
    {
        $redirectTo = $request->input('redirect_to');

        if ($redirectTo && is_string($redirectTo)) {
            $trimmed = trim($redirectTo);

            // Valid relative path: must start with single '/' and not protocol-relative '//' or Windows path '/\\'
            if (str_starts_with($trimmed, '/') && !str_starts_with($trimmed, '//') && !str_starts_with($trimmed, '/\\')) {
                return redirect($trimmed)->with($statusKey, $statusMsg);
            }

            // If absolute URL provided, verify host matches current request host exactly
            $host = parse_url($trimmed, PHP_URL_HOST);
            if ($host && strtolower($host) === strtolower($request->getHost())) {
                return redirect($trimmed)->with($statusKey, $statusMsg);
            }
        }

        if (!isset($routeParams['company_id'])) {
            try {
                $routeParams['company_id'] = $this->getCompanyId();
            } catch (\Throwable) {
                $routeParams['company_id'] = company_id() ?? session('company_id') ?? 1;
            }
        }

        return redirect()->route($fallbackRoute, $routeParams)->with($statusKey, $statusMsg);
    }

    /**
     * Mask customer phone number for non-admin/owner roles (e.g., 98****1234).
     */
    public static function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '—';
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);
        $len = strlen($clean);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        if ($len >= 10) {
            // Keep first 2 and last 4 digits
            return substr($clean, 0, 2) . str_repeat('*', $len - 6) . substr($clean, -4);
        }

        return substr($clean, 0, 1) . str_repeat('*', $len - 3) . substr($clean, -2);
    }

    /**
     * Mask device IMEI for non-admin/owner roles (e.g., 864818******123).
     */
    public static function maskImei(?string $imei): string
    {
        if (empty($imei)) {
            return '—';
        }

        $clean = trim($imei);
        $len = strlen($clean);

        if ($len < 10) {
            return substr($clean, 0, 2) . str_repeat('*', max(1, $len - 4)) . substr($clean, -2);
        }

        // Standard TAC prefix (6 digits) + masked serial + last 3 digits
        return substr($clean, 0, 6) . str_repeat('*', $len - 9) . substr($clean, -3);
    }

    /**
     * Resolve store owner's email address for security OTP notifications.
     */
    protected function getOwnerEmail(int $companyId): string
    {
        try {
            $owner = DB::table('users')
                ->join('user_companies', 'users.id', '=', 'user_companies.user_id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_companies.company_id', $companyId)
                ->whereIn('roles.name', ['store-admin', 'admin'])
                ->select('users.email')
                ->first();

            if ($owner && !empty($owner->email)) {
                return $owner->email;
            }
        } catch (\Throwable $e) {
            Log::warning("Could not query owner email via roles: " . $e->getMessage());
        }

        if (auth()->check() && auth()->user()->email) {
            return auth()->user()->email;
        }

        $company = DB::table('companies')->where('id', $companyId)->first();
        return $company?->email ?? config('mail.from.address', 'admin@mobileshop.local');
    }

    /**
     * Generate and dispatch a 6-digit OTP to the store owner's email.
     * Rate-limited to 3 requests per 5 minutes per user/IP.
     * Token is hashed using bcrypt before database persistence.
     */
    protected function generateOtp(int $companyId, string $action, string $itemReference, ?int $userId): array
    {
        $throttleKey = 'otp_gen:' . ($userId ?? auth()->id() ?? request()->ip()) . ':' . $action;

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return [
                'sent'         => false,
                'error'        => "Too many OTP requests. Please wait {$seconds} seconds before requesting a new code.",
                'available_in' => $seconds,
            ];
        }

        RateLimiter::hit($throttleKey, 300);

        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $ownerEmail = $this->getOwnerEmail($companyId);

        DB::table('ms_otp_tokens')->insert([
            'company_id'     => $companyId,
            'requested_by'   => $userId ?? (auth()->check() ? auth()->id() : 1),
            'action'         => $action,
            'item_reference' => $itemReference,
            'otp_code'       => Hash::make($otp),
            'expires_at'     => Carbon::now()->addMinutes(10),
            'created_at'     => Carbon::now(),
        ]);

        $readableAction = ucwords(str_replace('_', ' ', $action));
        $requesterName  = auth()->check() ? auth()->user()->name : "User #{$userId}";

        try {
            Mail::raw("Security Notice: Restricted Action '{$readableAction}' requested on item reference '{$itemReference}' by {$requesterName}.\n\nYour 6-digit Authorization Code is: {$otp}\n\nThis OTP expires in 10 minutes. If you did not authorize this action, do not disclose this code.", function ($message) use ($ownerEmail, $readableAction) {
                $message->to($ownerEmail)->subject("Security Authorization OTP: {$readableAction}");
            });
        } catch (\Throwable $e) {
            Log::warning("Failed to dispatch OTP email to {$ownerEmail}: " . $e->getMessage());
        }

        $maskedEmail = $ownerEmail;
        if (str_contains($ownerEmail, '@')) {
            $parts = explode('@', $ownerEmail);
            $maskedEmail = substr($parts[0], 0, 3) . '***@' . $parts[1];
        }

        return [
            'sent'         => true,
            'target_email' => $maskedEmail,
            'expires_in'   => 600,
        ];
    }

    /**
     * Verify OTP token for a specific action and item reference.
     * Uses constant-time hash comparison (Hash::check) with backward compatibility.
     */
    protected function verifyOtp(int $companyId, string $action, string $itemReference, ?string $code): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        if (empty($code)) {
            return false;
        }

        $submittedCode = trim($code);

        $tokens = DB::table('ms_otp_tokens')
            ->where('company_id', $companyId)
            ->where('action', $action)
            ->where('item_reference', $itemReference)
            ->where('expires_at', '>', Carbon::now())
            ->whereNull('verified_at')
            ->latest('id')
            ->limit(5)
            ->get();

        foreach ($tokens as $token) {
            if (Hash::check($submittedCode, $token->otp_code) || hash_equals($token->otp_code, $submittedCode)) {
                DB::table('ms_otp_tokens')->where('id', $token->id)->update([
                    'verified_at' => Carbon::now(),
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Generate Race-Free Atomic Sequential Invoice Number per Company
     */
    protected function getNextInvoiceNumber(int $companyId, string $prefix): string
    {
        return \App\Services\MobileShop\Common\MobileShopInvoiceHelper::getNextInvoiceNumber($companyId, $prefix);
    }

    /**
     * Resolve current store default state code (e.g., UP 09 or MH 27)
     */
    protected function getStoreStateCode(): string
    {
        return \App\Services\MobileShop\Common\MobileShopInvoiceHelper::getStoreStateCode();
    }

    /**
     * Find existing customer by phone or create a new customer record
     */
    protected function findOrCreateCustomer(int $companyId, Request $request): object
    {
        return \App\Services\MobileShop\Common\MobileShopInvoiceHelper::findOrCreateCustomer($companyId, $request);
    }

    /**
     * Calculate GST amounts and split based on store state and customer state
     */
    protected function calculateGst(float $salePrice, float $taxRate, string $billType, string $storeState, ?string $customerStateCode): array
    {
        return \App\Services\MobileShop\Common\MobileShopInvoiceHelper::calculateGst($salePrice, $taxRate, $billType, $storeState, $customerStateCode);
    }

    /**
     * Upload mobile device photo and return public relative path.
     * Enforces strict image MIME validation, ignores client extension, and generates UUID filenames.
     */
    protected function uploadMobilePhoto(Request $request, string $prefix): ?string
    {
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');

            // Strict MIME type verification via file contents
            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
            ];

            $mime = $file->getMimeType();
            if (!array_key_exists($mime, $allowedMimes)) {
                throw new \InvalidArgumentException('Invalid file type. Only JPG, PNG, WEBP, and GIF images are allowed.');
            }

            // Size guard: max 5MB
            if ($file->getSize() > 5 * 1024 * 1024) {
                throw new \InvalidArgumentException('Uploaded image exceeds the 5MB size limit.');
            }

            $ext = $allowedMimes[$mime];
            $filename = $prefix . '_' . Str::uuid()->toString() . '.' . $ext;

            $uploadDir = public_path('uploads/mobiles');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $filename);
            return 'uploads/mobiles/' . $filename;
        }

        return null;
    }

    /**
     * Resolve which niche a user belongs to.
     * Returns: 'admin' | 'phones' | 'secondhand' | 'accessories' | 'covers' | 'repairs' | 'none'
     */
    protected function getUserNiche(): string
    {
        $user = auth()->user();
        if (!$user) return 'none';
        if ($user->hasRole('store-admin') || $user->hasRole('admin')) return 'admin';
        if ($user->hasRole('sales-staff'))       return 'phones';
        if ($user->hasRole('secondhand-staff'))  return 'secondhand';
        if ($user->hasRole('accessories-staff') || $user->hasRole('accessories-manager')) return 'accessories';
        if ($user->hasRole('cover-staff'))       return 'covers';
        if ($user->hasRole('repair-technician')) return 'repairs';
        return 'none'; // Fail-closed security
    }

    /**
     * Helper to Convert Indian Rupees Amount to Words
     */
    public static function amountToWords($number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        $no = (int)$no;
        $str = [];
        $n_length = strlen((string)$no);
        $i = 0;
        while ($i < $n_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        $rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? " and " . ($words[$decimal / 10 * 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return 'Rupees ' . ($rupees ? trim($rupees) : 'Zero') . $paise . ' Only';
    }

    /**
     * Build Tally-Style Customer Ledger Statement (Chronological with running balance)
     */
    public function buildCustomerLedgerStatement(int $companyId, int $customerId): array
    {
        $customer = DB::table('ms_customers')->where('company_id', $companyId)->where('id', $customerId)->first();
        if (!$customer) {
            return [
                'customer' => null,
                'ledger' => collect([]),
                'totalBilled' => 0.00,
                'totalPaid' => 0.00,
                'closingBalance' => 0.00,
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

        // 2. Accessory Sales (Only include sales where a balance was left)
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
                DB::raw("'Accessory / Parts Counter Bill' as particulars"),
                'ms_accessory_sales.total_amount as billed_amount',
                'ms_accessory_sales.amount_paid as paid_amount',
                'ms_accessory_sales.udhari_amount as due_amount',
                DB::raw("'accessory_sale' as entry_type")
            )
            ->get();

        // 3. Khata Repayments & Adjustments
        $repayments = DB::table('ms_customer_khata_transactions')
            ->where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('type', '!=', 'udhari_sale')
            ->select(
                'id',
                'created_at',
                DB::raw("COALESCE(reference_no, CONCAT('KHATA-', id)) as ref_no"),
                DB::raw("COALESCE(remarks, 'Payment / Settlement Received') as particulars"),
                DB::raw("0.00 as billed_amount"),
                'amount as paid_amount',
                DB::raw("0.00 as due_amount"),
                'type as entry_type'
            )
            ->get();

        // Merge and sort chronologically
        $allEntries = $mobileSales->concat($accSales)->concat($repayments)->sortBy('created_at')->values();

        $runningBalance = 0.00;
        $totalBilled = 0.00;
        $totalPaid = 0.00;

        $ledger = $allEntries->map(function ($entry) use (&$runningBalance, &$totalBilled, &$totalPaid) {
            $billed = (float) $entry->billed_amount;
            $paid = (float) $entry->paid_amount;
            
            $totalBilled += $billed;
            $totalPaid += $paid;

            if ($entry->entry_type === 'adjustment') {
                $runningBalance = max(0.00, $runningBalance - $paid);
            } else {
                $runningBalance = max(0.00, $runningBalance + ($billed - $paid));
            }

            return (object) [
                'id' => $entry->id,
                'created_at' => $entry->created_at,
                'date' => date('d/m/Y', strtotime($entry->created_at)),
                'datetime' => date('d M Y, h:i A', strtotime($entry->created_at)),
                'ref_no' => $entry->ref_no,
                'particulars' => $entry->particulars,
                'billed' => $billed,
                'paid' => $paid,
                'balance_left' => $runningBalance,
                'entry_type' => $entry->entry_type,
            ];
        });

        return [
            'customer' => $customer,
            'ledger' => $ledger,
            'totalBilled' => $totalBilled,
            'totalPaid' => $totalPaid,
            'closingBalance' => (float) $customer->udhari_balance,
        ];
    }

    /**
     * Resolve Mobile Phone Sale details with all joined relations and gifts
     */
    protected function resolvePhoneSaleDetails(int $companyId, int $id): array
    {
        $sale = DB::table('ms_mobile_sales')
            ->join('ms_customers', 'ms_mobile_sales.customer_id', '=', 'ms_customers.id')
            ->join('ms_mobile_devices', 'ms_mobile_sales.device_id', '=', 'ms_mobile_devices.id')
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
        $customerStatement = $sale->customer_id ? $this->buildCustomerLedgerStatement($companyId, (int) $sale->customer_id) : null;

        return compact('sale', 'gifts', 'emiProvider', 'amountInWords', 'customerStatement');
    }

    /**
     * Resolve Accessory Sale details with customer and line items
     */
    protected function resolveAccessorySaleDetails(int $companyId, int $id): array
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

        $amountInWords = self::amountToWords($sale->total_amount);
        $customerStatement = $sale->customer_id ? $this->buildCustomerLedgerStatement($companyId, (int) $sale->customer_id) : null;

        return compact('sale', 'items', 'amountInWords', 'customerStatement');
    }

    /**
     * Resolve Purchase Order details with supplier and line items
     */
    protected function resolvePurchaseOrderDetails(int $companyId, int $id): ?array
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
