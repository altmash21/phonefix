<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Common\Company;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'read-admin-panel'                  => 'Read Admin Panel',
            'read-mobileshop-dashboard'         => 'View MobiTrack Dashboard (Niche-Scoped)',
            'read-mobileshop-purchase'          => 'View Purchase Page (own niche)',
            'create-purchase-phones'            => 'Add New Phone Purchase Invoice',
            'create-purchase-secondhand'        => 'Add Buyback / Pre-Owned Purchase Entry',
            'create-purchase-accessories'       => 'Add Accessories / Parts Purchase Invoice',
            'create-purchase-covers'            => 'Add Back Cover & Tempered Glass Purchase Invoice',
            'read-mobileshop-sales'             => 'View Sales Page (own niche)',
            'create-sale-phones'                => 'Create New Phone Sale Invoice',
            'create-sale-secondhand'            => 'Create Pre-Owned Sale Invoice',
            'create-sale-accessories'           => 'Create Accessories Sale Invoice',
            'create-sale-covers'                => 'Create Back Cover & Tempered Sale Invoice',
            'read-mobileshop-stock'             => 'View Stock Page (own niche only)',
            'manage-stock-phones'               => 'Add / Edit Brand New Phones Stock',
            'manage-stock-secondhand'           => 'Register Buyback & Manage Pre-Owned Stock',
            'manage-stock-accessories'          => 'Restock & Manage Accessories / Parts Catalog',
            'manage-stock-covers'               => 'Restock & Manage Back Covers & Tempered Glass',
            'manage-stock-repairs'              => 'Manage Service Desk & Update Repair Status',
            'read-mobileshop-reports'           => 'View Reports (own niche day book + stock valuation)',
            'read-reports-khata'                => 'View & Collect Customer Khata / Udhar',
            'read-reports-financial'            => 'View Full P&L, Margins & GST Summary (Admin Only)',
            'read-mobileshop-masters'           => 'Access Masters (Suppliers, Staff, Settings)',
            'read-mobileshop-pos'               => '[Legacy] Access New Phones POS Counter',
            'create-mobileshop-pos'             => '[Legacy] Create New Phone POS Sales',
            'read-mobileshop-new'               => '[Legacy] View Brand New Stock',
            'read-mobileshop-secondhand'        => '[Legacy] View Second-Hand Stock',
            'create-mobileshop-secondhand'      => '[Legacy] Register Second-Hand Buyback',
            'sell-mobileshop-secondhand'        => '[Legacy] Sell Pre-Owned Phones',
            'read-mobileshop-accessories'       => '[Legacy] View Accessories Catalog',
            'create-mobileshop-accessories'     => '[Legacy] Add/Restock Accessories',
            'sell-mobileshop-accessories'       => '[Legacy] Sell Accessories',
            'read-mobileshop-repairs'           => '[Legacy] Access Repair Desk',
            'update-mobileshop-repairs'         => '[Legacy] Manage Repairs',
            'read-mobileshop-khata'             => '[Legacy] View Customer Khata',
            'create-mobileshop-khata'           => '[Legacy] Collect Khata Repayments',
            'read-mobileshop-procurement'       => '[Legacy] View Purchase Orders',
            'create-mobileshop-procurement'     => '[Legacy] Make Supplier Payments',
            'void-mobileshop-sales'             => 'Void / Cancel Completed Sales (Admin Only)',
        ];

        foreach ($permissions as $name => $display) {
            Permission::firstOrCreate(['name' => $name], [
                'display_name' => $display,
                'description'  => $display,
            ]);
        }

        // 1. store-admin & core admin roles
        $allPermissions = Permission::all();
        $adminRoles = Role::whereIn('name', ['store-admin', 'admin'])->get();
        if ($adminRoles->isEmpty()) {
            $adminRoles = collect([
                Role::firstOrCreate(['name' => 'store-admin'], [
                    'display_name' => 'Store Owner / Admin',
                    'description'  => 'Full control over all MobiTrack ERP operations, analytics, settings and voiding',
                ])
            ]);
        }
        foreach ($adminRoles as $ar) {
            $ar->syncPermissions($allPermissions);
        }

        // 2. sales-staff
        $salesRole = Role::firstOrCreate(['name' => 'sales-staff'], [
            'display_name' => 'New Phones Sales Staff',
            'description'  => 'Brand new phone billing, IMEI tracking, and customer Khata — isolated to new phones niche',
        ]);
        $salesRole->syncPermissions(Permission::whereIn('name', [
            'read-admin-panel',
            'read-mobileshop-dashboard',
            'read-mobileshop-purchase',
            'create-purchase-phones',
            'read-mobileshop-sales',
            'create-sale-phones',
            'read-mobileshop-stock',
            'manage-stock-phones',
            'read-mobileshop-reports',
            'read-reports-khata',
            'read-mobileshop-pos',
            'create-mobileshop-pos',
            'read-mobileshop-new',
            'read-mobileshop-khata',
            'create-mobileshop-khata',
            'read-mobileshop-procurement',
            'create-mobileshop-procurement',
        ])->get());

        // 3. secondhand-staff
        $secondHandRole = Role::firstOrCreate(['name' => 'secondhand-staff'], [
            'display_name' => 'Second Hand & Buyback Specialist',
            'description'  => 'Pre-owned mobile intake, grading, buyback purchase, and pre-owned sales',
        ]);
        $secondHandRole->syncPermissions(Permission::whereIn('name', [
            'read-admin-panel',
            'read-mobileshop-dashboard',
            'read-mobileshop-purchase',
            'create-purchase-secondhand',
            'read-mobileshop-sales',
            'create-sale-secondhand',
            'read-mobileshop-stock',
            'manage-stock-secondhand',
            'read-mobileshop-reports',
            'read-mobileshop-secondhand',
            'create-mobileshop-secondhand',
            'sell-mobileshop-secondhand',
        ])->get());

        // 4. accessories-staff
        $accessoriesRole = Role::firstOrCreate(['name' => 'accessories-staff'], [
            'display_name' => 'Accessories & Parts Staff',
            'description'  => 'Full accessories catalog management including displays, ICs, charging pins, batteries, covers & glass',
        ]);
        $accessoriesRole->syncPermissions(Permission::whereIn('name', [
            'read-admin-panel',
            'read-mobileshop-dashboard',
            'read-mobileshop-purchase',
            'create-purchase-accessories',
            'create-purchase-covers',
            'read-mobileshop-sales',
            'create-sale-accessories',
            'create-sale-covers',
            'read-mobileshop-stock',
            'manage-stock-accessories',
            'manage-stock-covers',
            'read-mobileshop-reports',
            'read-mobileshop-accessories',
            'create-mobileshop-accessories',
            'sell-mobileshop-accessories',
        ])->get());

        // 5. cover-staff
        $coverRole = Role::firstOrCreate(['name' => 'cover-staff'], [
            'display_name' => 'Back Cover & Tempered Glass Staff',
            'description'  => 'Back covers and tempered glass management from a separate shop',
        ]);
        $coverRole->syncPermissions(Permission::whereIn('name', [
            'read-admin-panel',
            'read-mobileshop-dashboard',
            'read-mobileshop-purchase',
            'create-purchase-covers',
            'read-mobileshop-sales',
            'create-sale-covers',
            'read-mobileshop-stock',
            'manage-stock-covers',
            'read-mobileshop-reports',
        ])->get());

        // 6. repair-technician
        $techRole = Role::firstOrCreate(['name' => 'repair-technician'], [
            'display_name' => 'Repair Technician',
            'description'  => 'Job sheet execution, repair status updates, parts consumption',
        ]);
        $techRole->syncPermissions(Permission::whereIn('name', [
            'read-admin-panel',
            'read-mobileshop-dashboard',
            'read-mobileshop-stock',
            'manage-stock-repairs',
            'read-mobileshop-reports',
            'read-mobileshop-repairs',
            'update-mobileshop-repairs',
            'read-mobileshop-accessories',
        ])->get());

        // Ensure company 1 exists & is enabled
        $company = Company::first();
        if ($company) {
            $company->enabled = 1;
            $company->save();
            $companyId = $company->id;
        } else {
            $companyId = 1;
        }

        // Attach company and enable all existing users
        foreach (User::all() as $u) {
            $u->enabled = 1;
            if (empty($u->landing_page)) {
                $u->landing_page = 'mobileshop.dashboard';
            }
            $u->save();

            $u->companies()->syncWithoutDetaching([$companyId]);
        }

        // Flush Laratrust & general cache
        try {
            if (app()->bound('laratrust.cache')) {
                app('laratrust.cache')->flush();
            }
        } catch (\Throwable $e) {}
        try {
            Cache::flush();
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
    }
};
