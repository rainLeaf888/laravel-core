<?php

namespace App\Common\Facades;

use Illuminate\Support\Facades\Facade;

class YzResponse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'common.yzresponse';
    }
}
