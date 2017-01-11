<?php
/**
 * @file redis 查询
 * @author 郭金利
 */
namespace App\Common\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use App\Common\Zookeeper\Client;

class ZookeeperServiceProvider extends ServiceProvider
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
        $this->app->singleton(
            'common.zookeeper',
            function ($app) {
                $config = Arr::get($app['config']['crm'], 'zookeeper');
                return new Client(implode(",", $config));

            }
        );
    }

    public function provides()
    {
        return ['common.zookeeper'];
    }
}
