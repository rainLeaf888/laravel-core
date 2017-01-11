<?php
/**
 * 注册zk变量
 */
namespace App\Common\Facades;

use Illuminate\Support\Facades\Facade;

class YzZookeeper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'common.zookeeper';
    }
}
