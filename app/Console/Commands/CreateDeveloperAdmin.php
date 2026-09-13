<?php

namespace App\Console\Commands;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Common\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateDeveloperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mobileshop:setup-admin 
                            {--email=altmash@mobitrack.local : Email address for the admin account}
                            {--password=Password@12 : Password for the admin account}
                            {--name=Altmash : Display name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or reset developer/admin account and attach required company and roles for production';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = (string) $this->option('email');
        $password = (string) $this->option('password');
        $name = (string) $this->option('name');

        $this->info("Setting up Developer / Store Admin: {$email}...");

        $user = User::withoutEvents(function () use ($email, $name, $password) {
            return User::updateOrCreate(
                ['email' => $email],
                [
                    'name'         => $name,
                    'password'     => Hash::make($password),
                    'enabled'      => 1,
                    'landing_page' => 'dashboard',
                    'locale'       => 'en-GB',
                ]
            );
        });

        // Ensure company is linked (Akaunting requires user_companies association to allow login)
        $company = Company::first();
        if ($company) {
            $companyLinked = $user->companies()->where('company_id', $company->id)->exists();
            if (! $companyLinked) {
                $user->companies()->attach($company->id);
                $this->info("Linked user to company: {$company->name} (ID: {$company->id})");
            }
        } else {
            $this->warn("Warning: No company found in database. Create a company first.");
        }

        // Attach admin & store-admin roles
        $rolesToAttach = ['admin', 'store-admin'];
        foreach ($rolesToAttach as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role && ! $user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
                $this->info("Attached role: {$roleName}");
            }
        }

        // Also ensure admin@mobitrack.local exists and is ready
        if ($email !== 'admin@mobitrack.local') {
            $adminUser = User::withoutEvents(function () use ($password) {
                return User::updateOrCreate(
                    ['email' => 'admin@mobitrack.local'],
                    [
                        'name'         => 'Store Admin',
                        'password'     => Hash::make($password),
                        'enabled'      => 1,
                        'landing_page' => 'dashboard',
                        'locale'       => 'en-GB',
                    ]
                );
            });

            if ($company && ! $adminUser->companies()->where('company_id', $company->id)->exists()) {
                $adminUser->companies()->attach($company->id);
            }

            foreach ($rolesToAttach as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role && ! $adminUser->roles()->where('role_id', $role->id)->exists()) {
                    $adminUser->roles()->attach($role->id);
                }
            }
            $this->info("Also verified default 'admin@mobitrack.local' account.");
        }

        $this->newLine();
        $this->info("SUCCESS! Developer & Admin accounts are configured and ready.");
        $this->table(
            ['Field', 'Value'],
            [
                ['Staff ID / Username', 'altmash'],
                ['Email', $email],
                ['Password', $password],
                ['Status', 'Enabled (Active)'],
                ['Company Linked', $company ? "Yes ({$company->name})" : 'None'],
            ]
        );

        return 0;
    }
}
