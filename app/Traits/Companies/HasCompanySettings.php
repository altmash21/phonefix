<?php

namespace App\Traits\Companies;

trait HasCompanySettings
{
    public function setCommonSettingsAsAttributes()
    {
        try { // TODO will optimize..
            $settings = $this->settings;

            $groups = [
                'company',
                'default',
            ];

            foreach ($settings as $setting) {
                list($group, $key) = explode('.', $setting->getAttribute('key'));

                // Load only general settings
                if (! in_array($group, $groups)) {
                    continue;
                }

                $value = $setting->getAttribute('value');

                if (($key == 'logo') && empty($value)) {
                    $value = 'public/img/company.png';
                }

                $this->setAttribute($key, $value);
            }

            // Set default default company logo if empty
            if ($this->getAttribute('logo') == '') {
                $this->setAttribute('logo', 'public/img/company.png');
            }

            // Set default default company currency if empty
            if ($this->getAttribute('currency') == '') {
                $this->setAttribute('currency', config('setting.fallback.default.currency'));
            }
        } catch (\Throwable $e) {

        }
    }

    public function unsetCommonSettingsFromAttributes()
    {
        try { // TODO will optimize..
            $settings = $this->settings;

            $groups = [
                'company',
                'default',
            ];

            foreach ($settings as $setting) {
                list($group, $key) = explode('.', $setting->getAttribute('key'));

                // Load only general settings
                if (! in_array($group, $groups)) {
                    continue;
                }

                $this->offsetUnset($key);
            }

            // Always strip virtual attributes that are stored in settings, not in the companies table
            $virtualAttributes = [
                'name', 'email', 'locale', 'currency', 'logo',
                'phone', 'address', 'city', 'state', 'country',
                'zip_code', 'tax_number',
            ];

            foreach ($virtualAttributes as $attr) {
                $this->offsetUnset($attr);
            }
        } catch (\Throwable $e) {

        }
    }
}
