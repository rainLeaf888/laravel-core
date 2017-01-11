<?php
/**
 * 注册redis变量
 */
namespace App\Common\Facades;

use Illuminate\Support\Facades\Facade;

class YzRedis extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'common.yzredis';
    }
}
