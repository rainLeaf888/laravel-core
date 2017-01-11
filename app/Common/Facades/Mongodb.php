<?php
/**
 * 注册mongodb变量
 */
namespace App\Common\Facades;

use Illuminate\Support\Facades\Facade;

class Mongodb extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'common.mongodb';
    }
}
