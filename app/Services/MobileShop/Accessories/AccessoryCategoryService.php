<?php

namespace App\Services\MobileShop\Accessories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccessoryCategoryService
{
    /**
     * Default standard system categories
     */
    public static function defaultCategories(): array
    {
        return [
            ['name' => 'Display Screen / Folder', 'slug' => 'folder_display', 'is_gift_eligible' => 0],
            ['name' => 'Front Glass', 'slug' => 'front_glass', 'is_gift_eligible' => 0],
            ['name' => 'Charging Pin / Port', 'slug' => 'charging_port', 'is_gift_eligible' => 0],
            ['name' => 'IC / Motherboard Chip', 'slug' => 'ic_chip', 'is_gift_eligible' => 0],
            ['name' => 'Battery', 'slug' => 'battery', 'is_gift_eligible' => 0],
            ['name' => 'Back Panel / Housing Glass', 'slug' => 'back_panel', 'is_gift_eligible' => 0],
            ['name' => 'Back Cover & Cases', 'slug' => 'back_cover', 'is_gift_eligible' => 1],
            ['name' => 'Tempered Glass', 'slug' => 'tempered_glass', 'is_gift_eligible' => 1],
            ['name' => 'Chargers & Cables', 'slug' => 'charger_cable', 'is_gift_eligible' => 1],
            ['name' => 'Earphones & Audio', 'slug' => 'earphones_audio', 'is_gift_eligible' => 1],
            ['name' => 'Camera Module', 'slug' => 'camera_module', 'is_gift_eligible' => 0],
            ['name' => 'General Accessories', 'slug' => 'general_accessory', 'is_gift_eligible' => 1],
        ];
    }

    /**
     * Get all categories for company (auto-seeds defaults if none exist)
     */
    public function getCategories(int $companyId)
    {
        $categories = DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();

        if ($categories->isEmpty()) {
            $now = now();
            foreach (self::defaultCategories() as $cat) {
                DB::table('ms_part_categories')->insert([
                    'company_id'       => $companyId,
                    'name'             => $cat['name'],
                    'slug'             => $cat['slug'],
                    'is_gift_eligible' => $cat['is_gift_eligible'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }

            $categories = DB::table('ms_part_categories')
                ->where('company_id', $companyId)
                ->orderBy('name', 'asc')
                ->get();
        }

        return $categories;
    }

    /**
     * Resolve any raw category string or item name into a valid ms_part_categories row
     */
    public static function resolveCategory(int $companyId, ?string $input, ?string $itemName = null): ?object
    {
        $raw = trim((string) $input);
        if (empty($raw) && !empty($itemName)) {
            $raw = self::canonicalSlug('', $itemName);
        }

        if (empty($raw)) {
            $raw = 'general_accessory';
        }

        // 1. Direct match on slug or name
        $row = DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->where(function ($q) use ($raw) {
                $q->where('slug', $raw)->orWhere('name', $raw);
            })
            ->first();

        if ($row) {
            return $row;
        }

        // 2. Canonical slug resolution
        $canonical = self::canonicalSlug($raw, $itemName);

        $row = DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->where(function ($q) use ($canonical) {
                $q->where('slug', $canonical)
                  ->orWhere('slug', 'LIKE', "%{$canonical}%")
                  ->orWhere('name', 'LIKE', "%{$canonical}%");
            })
            ->first();

        if ($row) {
            return $row;
        }

        // 3. Fallback to general_accessory or first available
        $fallback = DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->where('slug', 'general_accessory')
            ->first();

        return $fallback ?: DB::table('ms_part_categories')->where('company_id', $companyId)->first();
    }

    /**
     * Convert any alias, keyword, or OCR term into standard DB slug
     */
    public static function canonicalSlug(?string $input, ?string $itemName = null): string
    {
        $combined = strtolower(trim(($input ?? '') . ' ' . ($itemName ?? '')));
        $slug = strtolower(trim($input ?? ''));

        if (preg_match('/folder|combo|display|screen|lcd|oled|tft|touch\s*display|touch\s*screen/', $combined) || in_array($slug, ['display_folder', 'folder_display'])) {
            return 'folder_display';
        }
        if (preg_match('/front.*glass|outer\s*glass|touch\s*glass|touchpad|oca/', $combined) || $slug === 'front_glass') {
            return 'front_glass';
        }
        if (preg_match('/pin|charging|port|connector|jack|cc\s*board|sub\s*board/', $combined) || in_array($slug, ['charging_pin', 'charging_port'])) {
            return 'charging_port';
        }
        if (preg_match('/\bic\b|motherboard|power\s*ic|charging\s*ic|audio\s*ic|wtr|pmic|cpu|chip/', $combined) || in_array($slug, ['ic_motherboard', 'ic_chip'])) {
            return 'ic_chip';
        }
        if (preg_match('/battery|cell|\bmah\b/', $combined) || $slug === 'battery') {
            return 'battery';
        }
        if (preg_match('/back\s*panel|housing|rear\s*panel|body\s*panel|housing\s*glass/', $combined) || $slug === 'back_panel') {
            return 'back_panel';
        }
        if (preg_match('/cover|case|smoke|bumper|pouch|silicone|leather|matte\s*case|skin/', $combined) || in_array($slug, ['back_cover_case', 'back_cover'])) {
            return 'back_cover';
        }
        if (preg_match('/tempered|11d|9d|21d|uv\s*glass|d\+|screen\s*guard|screen\s*protector|privacy\s*glass/', $combined) || $slug === 'tempered_glass') {
            return 'tempered_glass';
        }
        if (preg_match('/charger|cable|adapter|type-c|lightning|micro\s*usb|braided|fast\s*charg|power\s*bank|pd\s*cable|dock|usb\s*cord/', $combined) || in_array($slug, ['charger', 'cable', 'charger_cable'])) {
            return 'charger_cable';
        }
        if (preg_match('/earphone|headphone|audio|buds|airpod|neckband|tws|bluetooth\s*headset|speaker|mic\b|aux|sound|handsfree/', $combined) || in_array($slug, ['audio', 'earphones_audio'])) {
            return 'earphones_audio';
        }
        if (preg_match('/camera|lens/', $combined) || $slug === 'camera_module') {
            return 'camera_module';
        }

        return !empty($slug) ? $slug : 'general_accessory';
    }

    /**
     * Store Custom Part Category
     */
    public function storeCategory(int $companyId, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $slug = Str::slug($request->name, '_');

        $exists = DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return [
                'success' => false,
                'status'  => 422,
                'message' => 'Category already exists',
            ];
        }

        $catId = DB::table('ms_part_categories')->insertGetId([
            'company_id'       => $companyId,
            'name'             => $request->name,
            'slug'             => $slug,
            'is_gift_eligible' => $request->has('is_gift_eligible') ? 1 : 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return [
            'success' => true,
            'id'      => $catId,
            'message' => "Category '{$request->name}' added successfully!",
        ];
    }

    /**
     * Delete Custom Part Category (Gated by Owner OTP)
     */
    public function deleteCategory(int $companyId, int $id, Request $request, bool $isOwner, callable $verifyOtpCallback)
    {
        $itemRef = "category:{$id}";
        if (!$isOwner) {
            $otpCode = $request->input('otp_code');
            if (!$verifyOtpCallback($companyId, 'delete_category', $itemRef, $otpCode)) {
                return [
                    'success'        => false,
                    'otp_required'   => true,
                    'action'         => 'delete_category',
                    'item_reference' => $itemRef,
                    'message'        => 'Store Owner OTP authorization is required to delete product categories.',
                ];
            }
        }

        DB::table('ms_part_categories')->where('company_id', $companyId)->where('id', $id)->delete();

        return [
            'success' => true,
            'message' => 'Category deleted successfully!',
        ];
    }
}
