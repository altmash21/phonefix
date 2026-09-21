<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Common\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure primary company is enabled
        $company = Company::first();
        if ($company) {
            $company->enabled = 1;
            $company->save();
            $companyId = $company->id;
        } else {
            $companyId = 1;
        }

        // 2. Fetch or create store-admin and admin roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Admin',
            'description'  => 'Full System Administrator',
        ]);

        $storeAdminRole = Role::firstOrCreate(['name' => 'store-admin'], [
            'display_name' => 'Store Owner / Admin',
            'description'  => 'Full control over all MobiTrack ERP operations, analytics, settings and voiding',
        ]);

        $roleIds = array_filter([$adminRole?->id, $storeAdminRole?->id]);

        // 3. Create or update Developer Account (altmash)
        $altmashUser = User::where('email', 'altmash@mobitrack.local')
            ->orWhere('name', 'altmash')
            ->first();

        if (! $altmashUser) {
            $altmashUser = new User();
            $altmashUser->email = 'altmash@mobitrack.local';
        }

        $altmashUser->name         = 'altmash';
        $altmashUser->password     = Hash::make('Password@12');
        $altmashUser->enabled      = 1;
        $altmashUser->landing_page = 'dashboard';
        $altmashUser->locale       = 'en-GB';
        $altmashUser->save();

        $altmashUser->companies()->syncWithoutDetaching([$companyId]);
        $altmashUser->syncRoles($roleIds);

        // 4. Create or update Default Store Admin Account (admin@mobitrack.local)
        $adminUser = User::where('email', 'admin@mobitrack.local')->first();
        if (! $adminUser) {
            $adminUser = new User();
            $adminUser->email = 'admin@mobitrack.local';
        }

        $adminUser->name         = 'Store Admin';
        $adminUser->password     = Hash::make('Password@12');
        $adminUser->enabled      = 1;
        $adminUser->landing_page = 'dashboard';
        $adminUser->locale       = 'en-GB';
        $adminUser->save();

        $adminUser->companies()->syncWithoutDetaching([$companyId]);
        $adminUser->syncRoles($roleIds);

        // Flush permissions cache
        Cache::forget('spatie.permission.cache');
        Cache::flush();
    }

    public function down(): void
    {
        // Keep credentials intact on rollback
    }
};
