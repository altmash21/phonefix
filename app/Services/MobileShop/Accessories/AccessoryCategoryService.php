<?php

namespace App\Services\MobileShop\Accessories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccessoryCategoryService
{
    /**
     * Get all categories for company
     */
    public function getCategories(int $companyId)
    {
        return DB::table('ms_part_categories')
            ->where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();
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
