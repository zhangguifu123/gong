<?php

namespace App\Http\Middleware\User;

use App\User;
use Closure;

/** 已废弃！！！！！！！专用Auth/RefreshToken认证登录！！！！！！！ 2021.4.4 张桂福 */
class LoginCheck
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
        $authorization = $request->header('Authorization');
        if((isset($authorization) && $authorization !=null)||(session()->has('ManagerLogin') && session('ManagerLogin') === true)) {
            var_dump(1);
            return $next($request);
        }else{
                return  response(msg(6, __LINE__));
        }
    }
}
