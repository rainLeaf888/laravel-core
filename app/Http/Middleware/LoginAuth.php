<?php

namespace App\Http\Middleware;

use Closure;

class LoginAuth
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //将页面输入统一增加过滤空格功能
        $request->merge(array_map('trim', $request->all()));
        //过滤掉前端传入的undefined的值
        $request->merge(array_map(function ($value) {
            if ($value == 'undefined') {
                return null;
            } else {
                return $value;
            }
        }, $request->all()));
        
        return $next($request);
    }
}
