<?php

namespace Database\Seeds;

use App\Abstracts\Model;
use App\Models\Banking\Account;
use App\Traits\Jobs;
use Illuminate\Database\Seeder;

class Accounts extends Seeder
{
    use Jobs;

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

        $account = Account::create([
            'company_id' => $company_id,
            'name' => trans('demo.accounts.cash'),
            'number' => '1',
            'currency_code' => 'USD',
            'bank_name' => trans('demo.accounts.cash'),
            'enabled' => '1',
            'created_from' => 'core::seed',
        ]);

        setting()->set('default.account', $account->id);
    }
}
