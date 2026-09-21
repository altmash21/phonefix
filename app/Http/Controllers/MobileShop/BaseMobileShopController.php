<?php

namespace App\Http\Controllers\MobileShop;

use App\Services\MobileShop\Common\MobileShopInvoiceHelper;
use App\Services\MobileShop\Common\MobileShopInvoiceResolver;
use App\Services\MobileShop\Common\MobileShopOtpService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

abstract class BaseMobileShopController extends Controller
{
    /**
     * Accessories categories filter — back covers + tempered glass.
     */
    protected $coverCategories = ['back_cover', 'back_panel', 'tempered_glass'];

    protected ?int $currentCompanyId = null;

    /**
     * Resolve current company context (Fail-Closed Multi-Tenancy)
     */
    protected function getCompanyId(): int
    {
        if ($this->currentCompanyId !== null) {
            return $this->currentCompanyId;
        }

        // Fast path: Akaunting middleware already set the company in the container.
        // Use it directly — no DB query needed.
        $fromContainer = company_id();
        if ($fromContainer) {
            return $this->currentCompanyId = (int) $fromContainer;
        }

        // Slow path fallback: container not set, try session then user relationship.
        $companyId = session('company_id')
            ?? (auth()->check() ? (auth()->user()->company_id ?? auth()->user()->companies()->first()?->id) : null);

        if (!$companyId) {
            abort(403, 'Multi-tenant context error: No active company found in session.');
        }

        // Only run the expensive authorization check in the slow path (first request for this company).
        if (auth()->check() && !auth()->user()->companies()->where('company_id', $companyId)->exists()) {
            abort(403, 'Unauthorized company access attempt.');
        }

        return $this->currentCompanyId = (int) $companyId;
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

        return substr($clean, 0, 6) . str_repeat('*', $len - 9) . substr($clean, -3);
    }

    /**
     * Resolve store owner's email address for security OTP notifications.
     */
    protected function getOwnerEmail(int $companyId): string
    {
        return MobileShopOtpService::getOwnerEmail($companyId);
    }

    /**
     * Generate and dispatch a 6-digit OTP to the store owner's email.
     */
    protected function generateOtp(int $companyId, string $action, string $itemReference, ?int $userId): array
    {
        return MobileShopOtpService::generateOtp($companyId, $action, $itemReference, $userId);
    }

    /**
     * Verify OTP token for a specific action and item reference.
     */
    protected function verifyOtp(int $companyId, string $action, string $itemReference, ?string $code): bool
    {
        return MobileShopOtpService::verifyOtp($companyId, $action, $itemReference, $code, $this->isOwner());
    }

    /**
     * Generate Race-Free Atomic Sequential Invoice Number per Company
     */
    protected function getNextInvoiceNumber(int $companyId, string $prefix): string
    {
        return MobileShopInvoiceHelper::getNextInvoiceNumber($companyId, $prefix);
    }

    /**
     * Resolve current store default state code (e.g., UP 09 or MH 27)
     */
    protected function getStoreStateCode(): string
    {
        return MobileShopInvoiceHelper::getStoreStateCode();
    }

    /**
     * Find existing customer by phone or create a new customer record
     */
    protected function findOrCreateCustomer(int $companyId, Request $request): object
    {
        return MobileShopInvoiceHelper::findOrCreateCustomer($companyId, $request);
    }

    /**
     * Calculate GST amounts and split based on store state and customer state
     */
    protected function calculateGst(float $salePrice, float $taxRate, string $billType, string $storeState, ?string $customerStateCode): array
    {
        return MobileShopInvoiceHelper::calculateGst($salePrice, $taxRate, $billType, $storeState, $customerStateCode);
    }

    /**
     * Upload mobile device photo and return public relative path.
     */
    protected function uploadMobilePhoto(Request $request, string $prefix, string $fieldName = 'photo'): ?string
    {
        if ($request->hasFile($fieldName) && $request->file($fieldName)->isValid()) {
            $file = $request->file($fieldName);

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
     * Returns: 'admin' | 'accessories' | 'repairs' | 'none'
     */
    protected function getUserNiche(): string
    {
        $user = auth()->user();
        if (!$user) return 'none';
        if ($user->hasRole('store-admin') || $user->hasRole('admin')) return 'admin';
        if ($user->hasRole('accessories-staff') || $user->hasRole('accessories-manager')) return 'accessories';
        if ($user->hasRole('repair-technician')) return 'repairs';
        return 'none';
    }

    /**
     * Helper to Convert Indian Rupees Amount to Words
     */
    public static function amountToWords($number): string
    {
        return MobileShopInvoiceResolver::amountToWords($number);
    }

    /**
     * Build Tally-Style Customer Ledger Statement
     */
    public function buildCustomerLedgerStatement(int $companyId, int $customerId): array
    {
        return MobileShopInvoiceResolver::buildCustomerLedgerStatement($companyId, $customerId);
    }

    /**
     * Resolve Mobile Phone Sale details with all joined relations and gifts
     */
    protected function resolvePhoneSaleDetails(int $companyId, int $id): array
    {
        return MobileShopInvoiceResolver::resolvePhoneSaleDetails($companyId, $id);
    }

    /**
     * Resolve Accessory Sale details with customer and line items
     */
    protected function resolveAccessorySaleDetails(int $companyId, int $id): array
    {
        return MobileShopInvoiceResolver::resolveAccessorySaleDetails($companyId, $id);
    }

    /**
     * Resolve Purchase Order details with supplier and line items
     */
    protected function resolvePurchaseOrderDetails(int $companyId, int $id): ?array
    {
        return MobileShopInvoiceResolver::resolvePurchaseOrderDetails($companyId, $id);
    }
}
