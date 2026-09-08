<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Auth\User;
use App\Models\Auth\Role;
use App\Models\Auth\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $companyId = 1;

        // Ensure roles exist
        $adminRole = Role::firstOrCreate(['name' => 'store-admin'], [
            'display_name' => 'Store Owner / Admin',
            'description'  => 'Full control over all MobiTrack ERP operations, analytics, settings and voiding',
        ]);

        $salesRole = Role::firstOrCreate(['name' => 'sales-staff'], [
            'display_name' => 'New Phones Sales Staff',
            'description'  => 'Brand new phone billing, IMEI tracking, and customer Khata — isolated to new phones niche',
        ]);

        $secondHandRole = Role::firstOrCreate(['name' => 'secondhand-staff'], [
            'display_name' => 'Second Hand & Buyback Specialist',
            'description'  => 'Pre-owned mobile intake, grading, buyback purchase, and pre-owned sales',
        ]);

        $accessoriesRole = Role::firstOrCreate(['name' => 'accessories-staff'], [
            'display_name' => 'Accessories & Parts Staff',
            'description'  => 'Full accessories catalog management including displays, ICs, charging pins, batteries, covers & glass',
        ]);

        $coverRole = Role::firstOrCreate(['name' => 'cover-staff'], [
            'display_name' => 'Back Cover & Tempered Glass Staff',
            'description'  => 'Back covers and tempered glass management from a separate shop',
        ]);

        $techRole = Role::firstOrCreate(['name' => 'repair-technician'], [
            'display_name' => 'Repair Technician',
            'description'  => 'Job sheet execution, repair status updates, parts consumption',
        ]);

        // Sync permissions for salesRole
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

        $staffUsers = [
            [
                'name'     => 'Store Admin',
                'email'    => 'admin@mobitrack.local',
                'password' => 'password',
                'role'     => $adminRole,
                'lookup'   => 'admin@mobitrack.local',
            ],
            [
                'name'     => 'Vikram (New Phones Sales)',
                'email'    => 'sales@mobitrack.local',
                'password' => 'password',
                'role'     => $salesRole,
                'lookup'   => 'sales@mobitrack.local',
                'alt_like' => '%vikram%',
            ],
            [
                'name'     => 'Anil (Second Hand Buyback)',
                'email'    => 'buyback@mobitrack.local',
                'password' => 'password',
                'role'     => $secondHandRole,
                'lookup'   => 'buyback@mobitrack.local',
            ],
            [
                'name'     => 'Aman (Accessories Counter)',
                'email'    => 'accessories@mobitrack.local',
                'password' => 'password',
                'role'     => $accessoriesRole,
                'lookup'   => 'accessories@mobitrack.local',
            ],
            [
                'name'     => 'Ravi (Back Cover & Tempered)',
                'email'    => 'cover@mobitrack.local',
                'password' => 'password',
                'role'     => $coverRole,
                'lookup'   => 'cover@mobitrack.local',
            ],
            [
                'name'     => 'Sameer (Repair Technician)',
                'email'    => 'tech@mobitrack.local',
                'password' => 'password',
                'role'     => $techRole,
                'lookup'   => 'tech@mobitrack.local',
            ],
        ];

        foreach ($staffUsers as $su) {
            $query = User::where('email', $su['lookup']);
            if (!empty($su['alt_like'])) {
                $query->orWhere('email', 'like', $su['alt_like']);
            }
            $user = $query->first();

            if (!$user) {
                $user = new User();
            }

            $user->name         = $su['name'];
            $user->email        = $su['email'];
            $user->password     = $su['password'];
            $user->landing_page = 'mobileshop.dashboard';
            $user->locale       = 'en-GB';
            $user->enabled      = 1;
            $user->save();

            if (!$user->companies()->where('company_id', $companyId)->exists()) {
                $user->companies()->attach($companyId);
            }

            if ($su['role']) {
                $user->syncRoles([$su['role']->id]);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive: do not delete user accounts on down
    }
};
