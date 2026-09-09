<?php

namespace App\Traits\Modules;

use App\Utilities\Info;

trait HasModuleState
{
    public function checkToken($apiKey)
    {
        $data = [
            'form_params' => [
                'token' => $apiKey,
            ]
        ];

        if (! $response = static::getResponse('POST', 'token/check', $data)) {
            return false;
        }

        $result = json_decode($response->getBody());

        return $result->success ? true : false;
    }


    public function getCoreVersion()
    {
        $data['query'] = Info::all();

        if (! $response = static::getResponse('GET', 'core/version', $data)) {
            return [];
        }

        return $response->json();
    }

    public function moduleExists($alias)
    {
        if (! module($alias) instanceof \Akaunting\Module\Module) {
            return false;
        }

        return true;
    }

    public function moduleIsEnabled($alias): bool
    {
        if (! $this->moduleExists($alias)) {
            return false;
        }

        return module($alias)->enabled();
    }

    public function moduleIsDisabled($alias): bool
    {
        return ! $this->moduleIsEnabled($alias);
    }

    /**
     * Whether the app is part of how Akaunting is expected to work, and so may
     * neither be disabled nor uninstalled.
     */
    public function moduleIsProtected($alias): bool
    {
        return in_array($alias, (array) config('module.protected', []));
    }


    public function registerModules(): void
    {
        app(\Akaunting\Module\Contracts\ActivatorInterface::class)->register();
    }
}
