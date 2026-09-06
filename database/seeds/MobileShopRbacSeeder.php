<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\Role;
use App\Models\Auth\Permission;
use App\Models\Auth\User;
use App\Models\Common\Company;
use App\Utilities\Installer;

class MobileShopRbacSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────────────
        // Ensure default company exists and is enabled
        // ─────────────────────────────────────────────────────────────────────
        if (Company::count() === 0) {
            try {
                Installer::createCompany('Maurya Mobile Store', 'admin@mobitrack.local', 'en-GB');
            } catch (\Throwable $e) {
                DB::table('companies')->insert([
                    'id'         => 1,
                    'domain'     => '',
                    'enabled'    => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $company = Company::first();
        $companyId = $company ? $company->id : 1;
        if ($company && !$company->enabled) {
            $company->enabled = 1;
            $company->save();
        }

        // ─────────────────────────────────────────────────────────────────────
        // PERMISSIONS — Fine-grained, niche-scoped
        // ─────────────────────────────────────────────────────────────────────
        $permissions = [
            'read-admin-panel'                  => 'Read Admin Panel',

            // Dashboard (all roles)
            'read-mobileshop-dashboard'         => 'View MobiTrack Dashboard (Niche-Scoped)',

            // Purchase — niche gating
            'read-mobileshop-purchase'          => 'View Purchase Page (own niche)',
            'create-purchase-phones'            => 'Add New Phone Purchase Invoice',
            'create-purchase-secondhand'        => 'Add Buyback / Pre-Owned Purchase Entry',
            'create-purchase-accessories'       => 'Add Accessories / Parts Purchase Invoice',
            'create-purchase-covers'            => 'Add Back Cover & Tempered Glass Purchase Invoice',

            // Sales — niche gating
            'read-mobileshop-sales'             => 'View Sales Page (own niche)',
            'create-sale-phones'                => 'Create New Phone Sale Invoice',
            'create-sale-secondhand'            => 'Create Pre-Owned Sale Invoice',
            'create-sale-accessories'           => 'Create Accessories Sale Invoice',
            'create-sale-covers'                => 'Create Back Cover & Tempered Sale Invoice',

            // Stock — niche gating
            'read-mobileshop-stock'             => 'View Stock Page (own niche only)',
            'manage-stock-phones'               => 'Add / Edit Brand New Phones Stock',
            'manage-stock-secondhand'           => 'Register Buyback & Manage Pre-Owned Stock',
            'manage-stock-accessories'          => 'Restock & Manage Accessories / Parts Catalog',
            'manage-stock-covers'               => 'Restock & Manage Back Covers & Tempered Glass',
            'manage-stock-repairs'              => 'Manage Service Desk & Update Repair Status',

            // Reports — niche gating
            'read-mobileshop-reports'           => 'View Reports (own niche day book + stock valuation)',
            'read-reports-khata'                => 'View & Collect Customer Khata / Udhar',
            'read-reports-financial'            => 'View Full P&L, Margins & GST Summary (Admin Only)',

            // Masters (admin only)
            'read-mobileshop-masters'           => 'Access Masters (Suppliers, Staff, Settings)',

            // Legacy permissions kept for backward-compat with old views
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

        // ─────────────────────────────────────────────────────────────────────
        // ROLE 1: Store Admin — Full access to everything
        // ─────────────────────────────────────────────────────────────────────
        $adminRole = Role::firstOrCreate(['name' => 'store-admin'], [
            'display_name' => 'Store Owner / Admin',
            'description'  => 'Full control over all MobiTrack ERP operations, analytics, settings and voiding',
        ]);
        $adminRole->syncPermissions(Permission::where('name', 'like', '%mobileshop%')
            ->orWhere('name', 'like', 'create-%')
            ->orWhere('name', 'like', 'manage-%')
            ->orWhere('name', 'like', 'read-%')
            ->orWhere('name', 'like', 'sell-%')
            ->orWhere('name', 'read-admin-panel')
            ->get());

        // ─────────────────────────────────────────────────────────────────────
        // ROLE 2: Sales Staff — Brand New Mobiles niche ONLY
        // ─────────────────────────────────────────────────────────────────────
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
            // legacy
            'read-mobileshop-pos',
            'create-mobileshop-pos',
            'read-mobileshop-new',
            'read-mobileshop-khata',
            'create-mobileshop-khata',
        ])->get());

        // ─────────────────────────────────────────────────────────────────────
        // ROLE 3: Second Hand / Buyback Staff — Pre-Owned niche ONLY
        // ─────────────────────────────────────────────────────────────────────
        $secondHandRole = Role::firstOrCreate(['name' => 'secondhand-staff'], [
            'display_name' => 'Second Hand & Buyback Specialist',
            'description'  => 'Pre-owned mobile intake, grading, buyback purchase, and pre-owned sales — isolated to second hand niche',
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
            // legacy
            'read-mobileshop-secondhand',
            'create-mobileshop-secondhand',
            'sell-mobileshop-secondhand',
        ])->get());

        // ─────────────────────────────────────────────────────────────────────
        // ROLE 4: Accessories Staff — ALL accessories (incl. covers/tempered)
        // ─────────────────────────────────────────────────────────────────────
        $accessoriesRole = Role::firstOrCreate(['name' => 'accessories-staff'], [
            'display_name' => 'Accessories & Parts Staff',
            'description'  => 'Full accessories catalog management including displays, ICs, charging pins, batteries, back covers & tempered glass',
        ]);
        $accessoriesRole->syncPermissions(Permission::whereIn('name', [
            'read-admin-panel',
            'read-mobileshop-dashboard',
            'read-mobileshop-purchase',
            'create-purchase-accessories',
            'create-purchase-covers',         // accessories staff manages covers too
            'read-mobileshop-sales',
            'create-sale-accessories',
            'create-sale-covers',             // accessories staff can sell covers too
            'read-mobileshop-stock',
            'manage-stock-accessories',
            'manage-stock-covers',            // accessories staff manages covers too
            'read-mobileshop-reports',
            // legacy
            'read-mobileshop-accessories',
            'create-mobileshop-accessories',
            'sell-mobileshop-accessories',
        ])->get());

        // ROLE 4B: Accessories Manager — Full oversight over all accessories, covers, tempered glass, & parts
        $accessoriesManagerRole = Role::firstOrCreate(['name' => 'accessories-manager'], [
            'display_name' => 'Accessories Manager',
            'description'  => 'Manager for all accessories, back covers, tempered glass, and spare parts catalog and sales',
        ]);
        $accessoriesManagerRole->syncPermissions(Permission::whereIn('name', [
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

        // ─────────────────────────────────────────────────────────────────────
        // ROLE 5: Cover Staff — Back Covers & Tempered Glass ONLY (separate shop)
        // ─────────────────────────────────────────────────────────────────────
        $coverRole = Role::firstOrCreate(['name' => 'cover-staff'], [
            'display_name' => 'Back Cover & Tempered Glass Staff',
            'description'  => 'Back covers and tempered glass management from a separate shop — strictly scoped to cover/tempered niche only',
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

        // ─────────────────────────────────────────────────────────────────────
        // ROLE 6: Repair Technician — Service Desk ONLY (no purchase / no sale)
        // ─────────────────────────────────────────────────────────────────────
        $techRole = Role::firstOrCreate(['name' => 'repair-technician'], [
            'display_name' => 'Repair Technician',
            'description'  => 'Job sheet execution, repair status updates, parts consumption — no purchase or sales access',
        ]);
        $techRole->syncPermissions(Permission::whereIn('name', [
            'read-admin-panel',
            'read-mobileshop-dashboard',
            'read-mobileshop-stock',
            'manage-stock-repairs',
            'read-mobileshop-reports',
            // legacy
            'read-mobileshop-repairs',
            'update-mobileshop-repairs',
            'read-mobileshop-accessories',    // legacy: allowed to view parts for repair consumption
        ])->get());

        // ─────────────────────────────────────────────────────────────────────
        // Default admin account (always present for login)
        // ─────────────────────────────────────────────────────────────────────
        $adminUser = User::where('email', 'admin@mobitrack.local')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name'         => 'Store Admin',
                'email'        => 'admin@mobitrack.local',
                'password'     => 'password',
                'landing_page' => 'mobileshop.dashboard',
                'locale'       => 'en-GB',
                'enabled'      => 1,
            ]);
        } else {
            $adminUser->name         = 'Store Admin';
            $adminUser->password     = 'password';
            $adminUser->enabled      = 1;
            $adminUser->landing_page = 'mobileshop.dashboard';
            $adminUser->save();
        }

        if (!$adminUser->companies()->where('company_id', $companyId)->exists()) {
            $adminUser->companies()->attach($companyId);
        }
        $adminUser->syncRoles([$adminRole->id]);

        // ─────────────────────────────────────────────────────────────────────
        // DEMO STAFF ACCOUNTS (Password: 'password' — rotate before production)
        // ─────────────────────────────────────────────────────────────────────
        $staffUsers = [
            [
                'name'     => 'Vikram (New Phones Sales)',
                'email'    => 'sales@mobitrack.local',
                'password' => 'password',
                'role'     => $salesRole,
            ],
            [
                'name'     => 'Anil (Second Hand Buyback)',
                'email'    => 'buyback@mobitrack.local',
                'password' => 'password',
                'role'     => $secondHandRole,
            ],
            [
                'name'     => 'Aman (Accessories Counter)',
                'email'    => 'accessories@mobitrack.local',
                'password' => 'password',
                'role'     => $accessoriesRole,
            ],
            [
                'name'     => 'Ravi (Back Cover & Tempered)',
                'email'    => 'cover@mobitrack.local',
                'password' => 'password',
                'role'     => $coverRole,
            ],
            [
                'name'     => 'Sameer (Repair Technician)',
                'email'    => 'tech@mobitrack.local',
                'password' => 'password',
                'role'     => $techRole,
            ],
        ];

        foreach ($staffUsers as $su) {
            $user = User::where('email', $su['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name'         => $su['name'],
                    'email'        => $su['email'],
                    'password'     => $su['password'],
                    'landing_page' => 'mobileshop.dashboard',
                    'locale'       => 'en-GB',
                    'enabled'      => 1,
                ]);
            } else {
                $user->name         = $su['name'];
                $user->password     = $su['password'];
                $user->enabled      = 1;
                $user->landing_page = 'mobileshop.dashboard';
                $user->save();
            }

            if (!$user->companies()->where('company_id', $companyId)->exists()) {
                $user->companies()->attach($companyId);
            }
            $user->syncRoles([$su['role']->id]);
        }
    }
}
