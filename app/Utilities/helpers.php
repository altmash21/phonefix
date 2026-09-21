<?php

use App\Models\Common\Company;
use App\Traits\Cloud;
use App\Traits\DateTime;
use App\Traits\Sources;
use App\Traits\Modules;
use App\Traits\SearchString;
use App\Utilities\Date;
use App\Utilities\Widgets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

if (! function_exists('user')) {
    /**
     * Get the authenticated user.
     *
     * @return \App\Models\Auth\User
     */
    function user()
    {
        return auth()->user();
    }
}

if (! function_exists('user_id')) {
    /**
     * Get id of current user.
     */
    function user_id(): int|null
    {
        return user()?->id;
    }
}

if (! function_exists('company_date_format')) {
    /**
     * Get the date format of company.
     */
    function company_date_format(): string
    {
        $date_time = new class() { use DateTime; };

        return $date_time->getCompanyDateFormat();
    }
}

if (! function_exists('company_date')) {
    /**
     * Format the given date based on company settings.
     */
    function company_date($date): string
    {
        return Date::parse($date)->format(company_date_format());
    }
}

if (! function_exists('show_widget')) {
    /**
     * Show a widget.
     *
     * @return string
     */
    function show_widget()
    {
        $arguments = func_get_args();

        $model = array_shift($arguments);

        return Widgets::show($model, ...$arguments);
    }
}

if (! function_exists('company')) {
    /**
     * Get current/any company model.
     */
    function company(int|null $id = null): Company|null
    {
        $company = null;

        if (is_null($id)) {
            $company = Company::getCurrent();
        }

        if (is_numeric($id)) {
            $company = Company::find($id);
        }

        return $company;
    }
}

if (! function_exists('module_is_enabled')) {
    /**
     * Check if a module is enabled.
     */
    function module_is_enabled(string $alias): bool
    {
        $module = new class() { use Modules; };

        return $module->moduleIsEnabled($alias);
    }
}

if (! function_exists('company_id')) {
    /**
     * Get id of current company.
     */
    function company_id(): int|null
    {
        return company()?->id;
    }
}

if (! function_exists('team')) {
    /**
     * Get team of current company.
     */
    function team()
    {
        return company()?->team() !== null ? company()?->team() : company()?->owner?->team();
    }
}

if (! function_exists('team_id')) {
    /**
     * Get id of current company team.
     */
    function team_id()
    {
        return team()?->id;
    }
}

if (! function_exists('should_queue')) {
    /**
     * Check if queue is enabled.
     */
    function should_queue(): bool
    {
        return config('queue.default') != 'sync';
    }
}

if (! function_exists('source_name')) {
    /**
     * Get the current source.
     */
    function source_name(string|null $alias = null): string
    {
        $tmp = new class() { use Sources; };

        return $tmp->getSourceName(null, $alias);
    }
}

if (! function_exists('cache_prefix')) {
    /**
     * Cache system added company_id prefix.
     */
    function cache_prefix(): string
    {
        return company_id() . '_';
    }
}

if (! function_exists('array_values_recursive')) {
    /**
     * Get array values recursively.
     */
    function array_values_recursive(array $array): array
    {
        $flat = [];

        foreach($array as $value) {
            if (is_array($value)) {
                $flat = array_merge($flat, array_values_recursive($value));
            } else {
                $flat[] = $value;
            }
        }

        return $flat;
    }
}

if (! function_exists('running_in_install')) {
    /**
     * Detect if application is running in queue.
     */
    function running_in_install(): bool
    {
        return request_is_install() && env('APP_INSTALLED', false) == false;
    }
}

if (! function_exists('running_in_queue')) {
    /**
     * Detect if application is running in queue.
     */
    function running_in_queue(): bool
    {
        return app()->runningConsoleCommand([
            'queue:work',
            'queue:listen',
            'horizon',
        ]);
    }
}

if (! function_exists('running_in_schedule')) {
    /**
     * Detect if application is running in schedule.
     */
    function running_in_schedule(): bool
    {
        return app()->runningConsoleCommand([
            'schedule:run',
            'schedule:work',
        ]);
    }
}

if (! function_exists('running_in_test')) {
    /**
     * Detect if application is running in test.
     */
    function running_in_test(): bool
    {
        return env_is_testing() && app()->runningInConsole();
    }
}

if (! function_exists('simple_icons')) {
    /**
     * Get the simple icon content
     */
    function simple_icons(string $name): string
    {
        $path = base_path('vendor/simple-icons/simple-icons/icons/' . $name . '.svg');

        return file_get_contents($path);
    }
}

if (! function_exists('default_currency')) {
    /**
     * Get the default currency code
     */
    function default_currency(): string
    {
        return setting('default.currency');
    }
}

if (! function_exists('env_is_production')) {
    /**
     * Determine if the application is in the production environment
     */
    function env_is_production(): bool
    {
        return config('app.env') === 'production';
    }
}

if (! function_exists('env_is_development')) {
    /**
     * Determine if the application is in the development environment
     */
    function env_is_development(): bool
    {
        return config('app.env') === 'development';
    }
}

if (! function_exists('env_is_build')) {
    /**
     * Determine if the application is in the build environment
     */
    function env_is_build(): bool
    {
        return config('app.env') === 'build';
    }
}

if (! function_exists('env_is_local')) {
    /**
     * Determine if the application is in the local environment
     */
    function env_is_local(): bool
    {
        return config('app.env') === 'local';
    }
}

if (! function_exists('env_is_testing')) {
    /**
     * Determine if the application is in the testing environment
     */
    function env_is_testing(): bool
    {
        return config('app.env') === 'testing';
    }
}

if (! function_exists('is_local_storage')) {
    /**
     * Determine if the storage is local.
     */
    function is_local_storage(): bool
    {
        $driver = config('filesystems.disks.' . config('filesystems.default') . '.driver');

        return $driver == 'local';
    }
}

if (! function_exists('is_cloud_storage')) {
    /**
     * Determine if the storage is cloud.
     */
    function is_cloud_storage(): bool
    {
        return ! is_local_storage();
    }
}

if (! function_exists('get_storage_path')) {
    /**
     * Get the path from the storage.
     */
    function get_storage_path(string $path = ''): string
    {
        return is_local_storage()
                ? storage_path($path)
                : Storage::path($path);
    }
}

if (! function_exists('user_model_class')) {
    function user_model_class(): string
    {
        return config('auth.providers.users.model');
    }
}

if (! function_exists('role_model_class')) {
    function role_model_class(): string
    {
        return config('laratrust.models.role');
    }
}

if (! function_exists('team_model_class')) {
    function team_model_class(): string
    {
        return config('laratrust.models.team');
    }
}

if (! function_exists('search_string_value')) {
    function search_string_value(string $name, string $default = '', string $input = ''): string|array
    {
        $search = new class() { use SearchString; };

        return $search->getSearchStringValue($name, $default, $input);
    }
}

if (! function_exists('is_cloud')) {
    function is_cloud(): bool
    {
        $cloud = new class() { use Cloud; };

        return $cloud->isCloud();
    }
}

if (! function_exists('request_is_install')) {
    function request_is_install(Request|null $request = null): bool
    {
        $r = $request ?: request();

        return $r->is('install/*');
    }
}

if (! function_exists('request_is_api')) {
    function request_is_api(Request|null $request = null): bool
    {
        $r = $request ?: request();

        return $r->is(config('api.prefix') . '/*');
    }
}

if (! function_exists('request_is_mcp')) {
    function request_is_mcp(Request|null $request = null): bool
    {
        $r = $request ?: request();

        return $r->is('mcp') || $r->is('mcp/*');
    }
}

if (! function_exists('request_is_oauth_api')) {
    /**
     * Returns true when the current request is an API call authenticated
     * via an OAuth Bearer token (as set by AuthenticateOnceWithDynamicApi middleware).
     */
    function request_is_oauth_api(Request|null $request = null): bool
    {
        $r = $request ?: request();

        return request_is_api($r) && $r->attributes->get('auth_method') === 'oauth';
    }
}

if (! function_exists('request_is_auth')) {
    function request_is_auth(Request|null $request = null): bool
    {
        $r = $request ?: request();

        return $r->is('auth/*');
    }
}

if (! function_exists('request_is_signed')) {
    function request_is_signed(Request|null $request = null, int $company_id = null): bool
    {
        if (is_null($company_id)) {
            return false;
        }

        $r = $request ?: request();

        return $r->is($company_id . '/signed/*');
    }
}

if (! function_exists('request_is_portal')) {
    function request_is_portal(Request|null $request = null, int $company_id = null): bool
    {
        if (is_null($company_id)) {
            return false;
        }

        $r = $request ?: request();

        return $r->is($company_id . '/portal') || $r->is($company_id . '/portal/*');
    }
}

if (! function_exists('calculation_to_quantity')) {
    function calculation_to_quantity($quantity)
    {
        return \App\Utilities\SafeMathEvaluator::evaluate((string) $quantity);
    }
}

if (! function_exists('store_name')) {
    function store_name(?string $default = null): string
    {
        return config('mobileshop.store_name')
            ?: (env('STORE_NAME')
            ?: (setting('company.name') ?: ($default ?? 'PhoneFix Azamgarh')));
    }
}

if (! function_exists('store_phone')) {
    function store_phone(?string $default = null): string
    {
        return config('mobileshop.store_phone')
            ?: (env('STORE_PHONE')
            ?: (setting('company.phone') ?: ($default ?? '+91 98765 43210')));
    }
}

if (! function_exists('store_whatsapp')) {
    function store_whatsapp(?string $default = null): string
    {
        return config('mobileshop.store_whatsapp')
            ?: (env('STORE_WHATSAPP')
            ?: store_phone($default));
    }
}

if (! function_exists('store_city')) {
    function store_city(?string $default = null): string
    {
        return config('mobileshop.store_city')
            ?: (env('STORE_CITY')
            ?: (setting('company.city') ?: ($default ?? 'Azamgarh')));
    }
}

if (! function_exists('store_state')) {
    function store_state(?string $default = null): string
    {
        return config('mobileshop.store_state')
            ?: (env('STORE_STATE')
            ?: (setting('company.state') ?: ($default ?? 'Uttar Pradesh')));
    }
}

if (! function_exists('store_pin')) {
    function store_pin(?string $default = null): string
    {
        return config('mobileshop.store_pin')
            ?: (env('STORE_PIN')
            ?: (setting('company.pin') ?: ($default ?? '400050')));
    }
}

if (! function_exists('store_address')) {
    function store_address(?string $default = null): string
    {
        $addr = config('mobileshop.store_address') ?: env('STORE_ADDRESS');
        if (!empty($addr)) {
            $city = store_city();
            $state = store_state();
            $pin = store_pin();

            $full = $addr;
            if (!empty($city) && !str_contains(strtolower($full), strtolower($city))) {
                $full .= ', ' . $city;
            }
            if (!empty($state) && !str_contains(strtolower($full), strtolower($state))) {
                $full .= ', ' . $state;
            }
            if (!empty($pin) && !str_contains($full, $pin)) {
                $full .= ' ' . $pin;
            }
            return $full;
        }

        return setting('company.address') ?: ($default ?? 'Shop #14, Linking Road, Bandra West, Mumbai 400050');
    }
}

if (! function_exists('store_address_short')) {
    function store_address_short(?string $default = null): string
    {
        $addr = config('mobileshop.store_address') ?: env('STORE_ADDRESS');
        if (!empty($addr)) {
            return $addr;
        }
        return setting('company.address') ?: ($default ?? 'Linking Road, Bandra (W)');
    }
}

if (! function_exists('store_gstin')) {
    function store_gstin(?string $default = null): string
    {
        return config('mobileshop.store_gstin')
            ?: (env('STORE_GSTIN')
            ?: (setting('company.tax_number') ?: (setting('company.gstin') ?: ($default ?? '09AAACA1234F1Z5'))));
    }
}

if (! function_exists('store_upi_id')) {
    function store_upi_id(?string $default = null): string
    {
        return config('mobileshop.store_upi_id')
            ?: (env('STORE_UPI_ID') ?: ($default ?? ''));
    }
}

if (! function_exists('store_landline')) {
    function store_landline(?string $default = null): string
    {
        return config('mobileshop.store_landline')
            ?: (env('STORE_LANDLINE') ?: ($default ?? ''));
    }
}

