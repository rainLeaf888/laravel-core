<?php

namespace Modules\Account\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function getIndex()
    {
        return view('welcome');
    }
}
