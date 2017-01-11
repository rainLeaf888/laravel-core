<?php

namespace Modules\Account\Providers;

use Illuminate\Support\ServiceProvider;

class AccountServiceProvider extends ServiceProvider
{
    protected $defer = true;
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->singleton('account.account', function ($app) {
        //     return new Account();
        // });
    }

    public function provides()
    {
        //return ['account.account'];
    }
}
