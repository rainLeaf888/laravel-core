<?php
/**
 * @file Mongodb数据库查询工具
 */
namespace App\Common\Providers;

use Illuminate\Support\ServiceProvider;
use Doctrine\MongoDB\Connection;
use Illuminate\Support\Arr;

class MongodbServiceProvider extends ServiceProvider
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
        $this->app->singleton('common.mongodb', function ($app) {
            $config = Arr::get($app['config']['crm'], 'mongodb');
            $server = $config['driver'] . "://" . $config['host'] . ':'. $config['port'];
            $mongodb = new Connection($server, $config['options'], $config['config'], null);
            return $mongodb->selectDatabase($config['dbname']);
        });
    }

    public function provides()
    {
        return ['common.mongodb'];
    }
}
