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
        if (! $company) {
            try {
                $company = Company::create([
                    'name'    => 'PhoneFix Azamgarh',
                    'domain'  => '',
                    'enabled' => 1,
                ]);
                $this->info("Created primary company: PhoneFix Azamgarh");
            } catch (\Throwable $e) {
                $company = null;
            }
        }

        if ($company) {
            $companyLinked = $user->companies()->where('company_id', $company->id)->exists();
            if (! $companyLinked) {
                $user->companies()->attach($company->id);
                $this->info("Linked user to company: {$company->name} (ID: {$company->id})");
            }
        }

        // Attach admin & store-admin roles
        $rolesToAttach = ['admin', 'store-admin'];
        foreach ($rolesToAttach as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName], [
                'display_name' => ucwords(str_replace('-', ' ', $roleName)),
                'description'  => 'Full store administration',
            ]);
            if ($role && ! $user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
                $this->info("Attached role: {$roleName}");
            }
        }

        // Also ensure admin accounts exist and are ready for both domains
        $adminEmails = ['admin@phonefixazamgarh.com', 'admin@mobitrack.local'];
        foreach ($adminEmails as $aEmail) {
            $adminUser = User::withoutEvents(function () use ($aEmail, $password) {
                return User::updateOrCreate(
                    ['email' => $aEmail],
                    [
                        'name'         => 'Store Admin',
                        'password'     => $password,
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
            $this->info("Verified admin account: {$aEmail}");
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
