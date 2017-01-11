<?php
namespace App\Common\Facades;

use Illuminate\Support\Facades\Facade;

class Domain extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'common.domain';
    }
}
