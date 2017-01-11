<?php
/**
 * @file redis 查询
 * @author 郭金利
 */
namespace App\Common\Providers;

use Illuminate\Support\ServiceProvider;
use App\Common\Redis\Client;

class RedisServiceProvider extends ServiceProvider
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
        $this->app->singleton('common.yzredis', function ($app) {
            return new Client();
        });
    }

    public function provides()
    {
        return ['common.yzredis'];
    }
}
