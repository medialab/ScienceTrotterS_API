<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Utils\RequestUtil;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        // ---> Register custome Request class.
        $this->app->singleton(RequestUtil::class, function () {
             return RequestUtil::capture();
        });
        $this->app->alias(RequestUtil::class, 'request');
    }
}
