<?php
/**
 * @file Mongodb数据库查询工具
 */
namespace App\Common\Providers;

use Illuminate\Support\ServiceProvider;
use App\Common\Tools;

class ToolsServiceProvider extends ServiceProvider
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
        $this->app->singleton('common.yzresponse', function ($app) {
            return new Tools\YzResponse();
        });

        $this->app->singleton('common.domain', function ($app) {
            return new Tools\Domain();
        });

        $this->app->singleton('common.statistic', function ($app) {
            return new Tools\Statistic();
        });
    }

    public function provides()
    {
        return ['common.yzresponse', 'common.domain', 'common.statistic'];
    }
}
