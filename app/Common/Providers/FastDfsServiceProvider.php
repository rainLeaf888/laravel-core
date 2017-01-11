<?php
/**
 * Created by PhpStorm.
 * User: yzslx
 * Date: 2016/1/5
 * Time: 13:00
 */

namespace App\Common\Providers;

use App\Common\FastDFS\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class FastDfsServiceProvider extends ServiceProvider
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
            'common.fastdfs',
            function ($app) {
                $config = Arr::get($app['config']['crm'], 'fastdfs');
                return new Client($config);

            }
        );
    }

    public function provides()
    {
        return ['common.fastdfs'];
    }
}
