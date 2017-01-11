<?php

namespace App\Common\Facades;

use Illuminate\Support\Facades\Facade;

class FastDfs extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'common.fastdfs';
    }
}
