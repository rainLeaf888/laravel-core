<?php
/**
 * 注册常用小工具
 */
namespace App\Common\Facades;

use Illuminate\Support\Facades\Facade;

class Statistic extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'common.statistic';
    }
}
