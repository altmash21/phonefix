<?php

namespace Database\Seeds {

    use App\Models\Common\Company;
    use App\Utilities\Installer;
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
                Installer::createCompany('PhoneFix Azamgarh', 'admin@phonefixazamgarh.com', 'en-GB');
            }

            $this->call(MobileShopRbacSeeder::class);
            $this->call(MobileShopDemoSeeder::class);
        }
    }
}

namespace {
    class DatabaseSeeder extends \Database\Seeds\DatabaseSeeder
    {
    }
}

