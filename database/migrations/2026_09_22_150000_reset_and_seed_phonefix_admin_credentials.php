<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Common\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure primary company exists and is named PhoneFix Azamgarh
        $company = Company::first();
        if (! $company) {
            try {
                $company = Company::create([
                    'name'       => 'PhoneFix Azamgarh',
                    'domain'     => '',
                    'enabled'    => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                $companyId = DB::table('companies')->insertGetId([
                    'domain'     => '',
                    'enabled'    => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $company = Company::find($companyId);
            }
        }

        if ($company) {
            $company->name = 'PhoneFix Azamgarh';
            $company->enabled = 1;
            $company->save();
            $companyId = $company->id;
        } else {
            $companyId = 1;
        }

        // Update company settings
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
        } catch (\Throwable $e) {}

        // 2. Roles & Permissions setup
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Admin',
            'description'  => 'Full System Administrator',
        ]);

        $storeAdminRole = Role::firstOrCreate(['name' => 'store-admin'], [
            'display_name' => 'Store Owner / Admin',
            'description'  => 'Full control over all PhoneFix Azamgarh ERP operations, analytics, settings and voiding',
        ]);

        $accessoriesRole = Role::firstOrCreate(['name' => 'accessories-staff'], [
            'display_name' => 'Accessories Staff',
            'description'  => 'Full accessories and spare parts catalog management, POS sales, and inventory restock',
        ]);

        $techRole = Role::firstOrCreate(['name' => 'repair-technician'], [
            'display_name' => 'Repair Technician',
            'description'  => 'Job sheet execution, repair status updates, parts consumption',
        ]);

        // Attach all available permissions to store-admin & admin
        try {
            $allPermissions = Permission::all();
            $storeAdminRole->syncPermissions($allPermissions);
            $adminRole->syncPermissions($allPermissions);
        } catch (\Throwable $e) {}

        // 3. Purge legacy/old staff accounts to avoid credential conflicts
        User::whereIn('email', [
            'sales@mobitrack.local',
            'buyback@mobitrack.local',
            'cover@mobitrack.local',
            'tech@mobitrack.local',
        ])->delete();

        // 4. Seed Fresh, Clean Credentials
        // Password for all accounts: Password@123
        $cleanPassword = 'Password@123';

        $accountsToSeed = [
            [
                'email'        => 'admin@phonefixazamgarh.com',
                'name'         => 'Store Admin',
                'landing_page' => 'dashboard',
                'role'         => $storeAdminRole,
            ],
            [
                'email'        => 'altmash@phonefixazamgarh.com',
                'name'         => 'altmash',
                'landing_page' => 'dashboard',
                'role'         => $storeAdminRole,
            ],
            [
                'email'        => 'accessories@phonefixazamgarh.com',
                'name'         => 'Accessories Staff',
                'landing_page' => 'mobileshop.accessories.pos',
                'role'         => $accessoriesRole,
            ],
            [
                'email'        => 'repair@phonefixazamgarh.com',
                'name'         => 'Repair Technician',
                'landing_page' => 'mobileshop.repairs',
                'role'         => $techRole,
            ],
            // Backward-compatibility aliases
            [
                'email'        => 'admin@mobitrack.local',
                'name'         => 'Store Admin',
                'landing_page' => 'dashboard',
                'role'         => $storeAdminRole,
            ],
            [
                'email'        => 'altmash@mobitrack.local',
                'name'         => 'altmash',
                'landing_page' => 'dashboard',
                'role'         => $storeAdminRole,
            ],
        ];

        foreach ($accountsToSeed as $acc) {
            $user = User::where('email', $acc['email'])->first();
            if (! $user) {
                $user = new User();
                $user->email = $acc['email'];
            }

            $user->name         = $acc['name'];
            $user->password     = $cleanPassword; // User model handles bcrypt hashing
            $user->enabled      = 1;
            $user->landing_page = $acc['landing_page'];
            $user->locale       = 'en-GB';
            $user->save();

            $user->companies()->syncWithoutDetaching([$companyId]);
            $user->syncRoles([$acc['role']->id]);
        }

        // Flush permissions and model caches
        Cache::forget('spatie.permission.cache');
        Cache::flush();
    }

    public function down(): void
    {
        // No destructive down needed
    }
};
