<?php

namespace EscolaLms\MailerLite\Providers;

use EscolaLms\MailerLite\Enum\PackageStatusEnum;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use EscolaLms\Settings\Facades\AdministrableConfig;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    const CONFIG_KEY = 'escolalms_mailer_lite';

    public function register()
    {
        if (class_exists(EscolaLmsSettingsServiceProvider::class)) {
            if (!$this->app->getProviders(EscolaLmsSettingsServiceProvider::class)) {
                $this->app->register(EscolaLmsSettingsServiceProvider::class);
            }
        }

        AdministrableConfig::registerConfig(self::CONFIG_KEY . '.package_status', ['required', 'string', 'in:' . implode(',', PackageStatusEnum::getValues())], false);
        AdministrableConfig::registerConfig(self::CONFIG_KEY . '.api_key', ['required', 'string'], false);
    }
}
