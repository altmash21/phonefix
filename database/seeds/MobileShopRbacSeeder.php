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
                Installer::createCompany('PhoneFix Azamgarh', 'admin@mobitrack.local', 'en-GB');
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
        if ($company) {
            if (empty($company->name) || in_array($company->name, ['Maurya Mobile Store', 'Maurya Mobile', 'MobiTrack Store', 'My Company'])) {
                $company->name = 'PhoneFix Azamgarh';
            }
            if (!$company->enabled) {
                $company->enabled = 1;
            }
            $company->save();
        }
        $companyId = $company ? $company->id : 1;

        // Ensure company settings reflect PhoneFix Azamgarh
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => 'company.name', 'company_id' => $companyId],
                ['value' => 'PhoneFix Azamgarh']
            );
            DB::table('settings')->updateOrInsert(
                ['key' => 'company.city', 'company_id' => $companyId],
                ['value' => 'Azamgarh']
            );
            DB::table('settings')->updateOrInsert(
                ['key' => 'company.state', 'company_id' => $companyId],
                ['value' => 'Uttar Pradesh']
            );
            DB::table('settings')->updateOrInsert(
                ['key' => 'company.pin', 'company_id' => $companyId],
                ['value' => '276001']
            );
        } catch (\Throwable $e) {
            // Ignore if settings table not yet ready
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
        // ROLE 1: Store Admin — Full access to everything in the system
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
        // ROLE 2: Accessories Staff — Accessories, covers, tempered glass, & parts
        // ─────────────────────────────────────────────────────────────────────
        $accessoriesRole = Role::firstOrCreate(['name' => 'accessories-staff'], [
            'display_name' => 'Accessories Staff',
            'description'  => 'Full accessories and spare parts catalog management, POS sales, and inventory restock',
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

        // ─────────────────────────────────────────────────────────────────────
        // ROLE 3: Repair Technician — Service Desk & Parts Usage ONLY
        // ─────────────────────────────────────────────────────────────────────
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

        // Clean up legacy roles if present
        Role::whereIn('name', ['sales-staff', 'secondhand-staff', 'cover-staff', 'accessories-manager'])->delete();

        // ─────────────────────────────────────────────────────────────────────
        // CLEAN PhoneFix Azamgarh ACCOUNTS & CREDENTIALS
        // ─────────────────────────────────────────────────────────────────────
        // Delete legacy/old accounts
        User::whereIn('email', [
            'sales@mobitrack.local',
            'buyback@mobitrack.local',
            'cover@mobitrack.local',
            'tech@mobitrack.local',
        ])->delete();

        $cleanPassword = 'Password@123';

        $accounts = [
            [
                'name'         => 'Store Admin',
                'email'        => 'admin@phonefixazamgarh.com',
                'password'     => $cleanPassword,
                'landing_page' => 'dashboard',
                'role'         => $adminRole,
            ],
            [
                'name'         => 'altmash',
                'email'        => 'altmash@phonefixazamgarh.com',
                'password'     => $cleanPassword,
                'landing_page' => 'dashboard',
                'role'         => $adminRole,
            ],
            [
                'name'         => 'Accessories Staff',
                'email'        => 'accessories@phonefixazamgarh.com',
                'password'     => $cleanPassword,
                'landing_page' => 'mobileshop.accessories.pos',
                'role'         => $accessoriesRole,
            ],
            [
                'name'         => 'Repair Technician',
                'email'        => 'repair@phonefixazamgarh.com',
                'password'     => $cleanPassword,
                'landing_page' => 'mobileshop.repairs',
                'role'         => $techRole,
            ],
            // Backward-compatibility aliases
            [
                'name'         => 'Store Admin',
                'email'        => 'admin@mobitrack.local',
                'password'     => $cleanPassword,
                'landing_page' => 'dashboard',
                'role'         => $adminRole,
            ],
            [
                'name'         => 'altmash',
                'email'        => 'altmash@mobitrack.local',
                'password'     => $cleanPassword,
                'landing_page' => 'dashboard',
                'role'         => $adminRole,
            ],
        ];

        foreach ($accounts as $acc) {
            $user = User::where('email', $acc['email'])->first();
            if (!$user) {
                $user = new User();
                $user->email = $acc['email'];
            }

            $user->name         = $acc['name'];
            $user->password     = $acc['password']; // User model setPasswordAttribute handles bcrypt
            $user->enabled      = 1;
            $user->landing_page = $acc['landing_page'];
            $user->locale       = 'en-GB';
            $user->save();

            $user->companies()->syncWithoutDetaching([$companyId]);
            $user->syncRoles([$acc['role']->id]);
        }
    }
}
