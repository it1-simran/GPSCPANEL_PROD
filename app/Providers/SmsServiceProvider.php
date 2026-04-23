<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Contracts\SmsProviderInterface::class, function ($app) {
            if (config('services.sms.provider') === 'plivo') {
                if (class_exists(\App\Services\Sms\PlivoProvider::class)) {
                    return new \App\Services\Sms\PlivoProvider();
                }
            }
            return new \App\Services\Sms\MockSmsProvider();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
