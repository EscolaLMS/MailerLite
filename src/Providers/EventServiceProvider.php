<?php

namespace EscolaLms\MailerLite\Providers;

use EscolaLms\Auth\Events\AccountBlocked;
use EscolaLms\Auth\Events\AccountConfirmed;
use EscolaLms\Cart\Events\CartOrderPaid;
use EscolaLms\MailerLite\Enum\GroupNamesEnum;
use EscolaLms\MailerLite\Enum\PackageStatusEnum;
use EscolaLms\MailerLite\Services\Contracts\MailerLiteServiceContract;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (Config::get(SettingsServiceProvider::CONFIG_KEY . '.package_status', PackageStatusEnum::ENABLED) !== PackageStatusEnum::ENABLED) {
            return;
        }

        Event::listen(AccountConfirmed::class, function ($event) {
            /**
             * >>> event(new EscolaLms\Auth\Events\AccountConfirmed(App\Models\User::find(18)));
             */
            $newsletterKey = Config::get(SettingsServiceProvider::CONFIG_KEY . '.newsletter_field_key');
            if ($event->user->settings->where('key', 'additional_field:' . $newsletterKey)->first()) {
                app(MailerLiteServiceContract::class)->addSubscriberToGroup(
                    Config::get(SettingsServiceProvider::CONFIG_KEY . '.group_registered_group', GroupNamesEnum::REGISTERED_USERS),
                    $event->user
                );
            }
        });

        Event::listen(CartOrderPaid::class, function ($event) {
            /**
             * >>> event(new EscolaLms\Cart\Events\CartOrderPaid (EscolaLms\Cart\Models\User::find(18), EscolaLms\Cart\Models\Order::find(1)));
             */
            app(MailerLiteServiceContract::class)->addSubscriberToGroup(
                Config::get(SettingsServiceProvider::CONFIG_KEY . '.group_order_paid', GroupNamesEnum::ORDER_PAID),
                $event->getUser()
            );
        });

        Event::listen(AccountBlocked::class, function ($event) {
            /**
             * >>> event(new EscolaLms\Auth\Events\AccountBlocked(App\Models\User::find(18)));
             */
            app(MailerLiteServiceContract::class)->deleteSubscriber($event->getUser());
        });
    }
}
