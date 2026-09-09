<?php

namespace App\Traits\Modules;

use App\Utilities\Date;
use App\Utilities\Versions;
use Illuminate\Support\Facades\Cache;

trait HasModuleSubscriptionsAndTips
{
    public function getModulesLimitOfSubscription()
    {
        $limit = new \stdClass();

        $limit->action_status = true;
        $limit->view_status = true;
        $limit->message = "Success";

        if (! config('app.installed') || running_in_test()) {
            return $limit;
        }

        if (is_cloud()) {
            return $limit;
        }

        $modules = module()->all();

        $versions = Versions::all($modules);

        foreach ($versions as $alias => $version) {
            if ($alias == 'core') {
                continue;
            }

            $module_limit = $this->getModuleLimitOfSubscription($alias, $version);

            if ($module_limit->action_status === false) {
                $limit->action_status = false;
                $limit->view_status = false;
                $limit->message = $module_limit->message;

                // Clear cache to reflect changes
                Cache::forget('updates');
                Cache::forget('versions');
            }
        }

        return $limit;
    }

    public function getModuleLimitOfSubscription($alias, $version = null)
    {
        $limit = new \stdClass();

        $limit->action_status = true;
        $limit->view_status = true;
        $limit->message = "Success";

        if (empty($version)) {
            $version = Versions::getVersionByAlias($alias);
        }

        if (! $version->subscription) {
            return $limit;
        }

        // A protected app stays in place whatever its subscription says, so there is
        // nothing to enforce and the disable/uninstall jobs would only throw.
        if ($this->moduleIsProtected($alias)) {
            return $limit;
        }

        if (! in_array($version->subscription->action_status, ['disabled', 'uninstalled'])) {
            return $limit;
        }

        $module_companies = Module::allCompanies()->enabled()->alias($alias)->get();

        if (! $module_companies->count()) {
            return $limit;
        }

        $limit->action_status = false;
        $limit->view_status = false;
        $limit->message = "Not able to app $alias.";

        foreach ($module_companies as $module) {
            switch ($version->subscription->action_status) {
                case 'disabled':
                    dispatch(new DisableModule($alias, $module->company_id));
                    break;
                case 'uninstalled':
                    dispatch(new UninstallModule($alias, $module->company_id));
                    break;
                default:
                    // Do nothing
                    break;
            }
        }

        return $limit;
    }

    public function loadSubscriptions()
    {
        $key = 'apps.subscriptions';

        return Cache::remember($key, Date::now()->addHours(6), function () {
            $data = [];

            if (! is_cloud()) {
                $data['headers'] = [
                    'X-Akaunting-Modules' => implode(',', module()->getAvailable()),
                ];
            }

            return (array) static::getResponseData('GET', 'apps/subscriptions', $data);
        });
    }

    public function loadSuggestions()
    {
        $key = 'apps.suggestions';

        return Cache::remember($key, Date::now()->addHours(6), function () {
            $data = [];

            $suggestions = (array) static::getResponseData('GET', 'apps/suggestions');

            foreach ($suggestions as $suggestion) {
                $data[$suggestion->path] = $suggestion;
            }

            return $data;
        });
    }

    public function loadNotifications()
    {
        $key = 'apps.notifications';

        return Cache::remember($key, Date::now()->addHours(6), function () {
            $data = [];

            $notifications = (array) static::getResponseData('GET', 'apps/notifications');

            foreach ($notifications as $notification) {
                $data[$notification->path][] = $notification;
            }

            return $data;
        });
    }

    public function loadTips()
    {
        $key = 'apps.tips';

        return Cache::remember($key, Date::now()->addHours(6), function () {
            $data = [];

            $tips = (array) static::getResponseData('GET', 'apps/tips');

            foreach ($tips as $tip) {
                $data[$tip->path][] = $tip;
            }

            return $data;
        });
    }

    public function getSubscription($alias)
    {
        $data = $this->loadSubscriptions();

        if (! empty($data) && array_key_exists($alias, $data)) {
            return $data[$alias];
        }

        return false;
    }

    public function getSuggestions($path)
    {
        $data = $this->loadSuggestions();

        if (! empty($data) && array_key_exists($path, $data)) {
            return $data[$path];
        }

        return false;
    }

    public function getNotifications($path): array
    {
        $data = $this->loadNotifications();

        if (! empty($data) && array_key_exists($path, $data)) {
            return (array) $data[$path];
        }

        return [];
    }

    public function getTips($path): array
    {
        $data = $this->loadTips();

        if (! empty($data) && array_key_exists($path, $data)) {
            return (array) $data[$path];
        }

        return [];
    }

}
