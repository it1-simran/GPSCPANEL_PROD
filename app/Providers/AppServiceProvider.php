<?php

namespace App\Providers;

use App\Support\GpsFlash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    public function boot()
    {
        Schema::defaultStringLength(191);
        Carbon::macro('utc', function () {
            return Carbon::now('UTC')->format('Y-m-d H:i:s');
        });

        View::composer(['layouts.apps', 'layouts.*'], function ($view) {
            $errors = $view->getData()['errors'] ?? View::shared('errors');
            $view->with('gpsPageFlash', GpsFlash::collect($errors));
        });
    }
}
