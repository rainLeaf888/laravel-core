<?php
/**
 * @file 作用域
 */
namespace App\Common\Tools;

use Illuminate\Support\Facades\Session;

class Domain
{
    /**
     * 获取所有Session的信息，统一调用方便修改
     * @return array
     */
    public function getSession()
    {
        $data = Session::get('userInfo');

        return $data;
    }
}
