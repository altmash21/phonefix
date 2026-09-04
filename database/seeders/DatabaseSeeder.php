<?php

namespace Database\Seeders;

use App\Models\Common\Company;
use App\Utilities\Installer;
use Database\Seeds\MobileShopDemoSeeder;
use Database\Seeds\MobileShopRbacSeeder;
use Database\Seeds\Permissions;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(Permissions::class);

        if (Company::count() === 0) {
            Installer::createCompany('MobiTrack Store', 'admin@mobitrack.local', 'en-GB');
        }

        $this->call(MobileShopRbacSeeder::class);
        $this->call(MobileShopDemoSeeder::class);
    }
}

