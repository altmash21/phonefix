<?php

namespace Database\Seeds;

use App\Abstracts\Model;
use App\Traits\Modules as ModulesTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class Modules extends Seeder
{
    use ModulesTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->create();

        Model::reguard();
    }

    private function create()
    {
        $company_id = $this->command ? $this->command->argument('company') : 1;
        $company_id = $company_id ?: 1;

        if ($this->moduleExists('offline-payments')) {
            Artisan::call('module:install', [
                'alias'     => 'offline-payments',
                'company'   => $company_id,
                'locale'    => session('locale', company($company_id)->locale ?? 'en-GB'),
            ]);
        }

        if ($this->moduleExists('paypal-standard')) {
            Artisan::call('module:install', [
                'alias'     => 'paypal-standard',
                'company'   => $company_id,
                'locale'    => session('locale', company($company_id)->locale ?? 'en-GB'),
            ]);
        }
    }
}
