<?php

namespace App\Http\Middleware\User;

use App\User;
use Closure;

class OwnerCheck
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
        $uid = handleUid($request);
        if($uid == $request->route("uid")) {
            return $next($request);
        } else {
            $remember = User::query()->where('remember','=',$request->header('remember'))->get()->toArray();
            if ($remember == null){
                // 未登录返回 未登录
                // 正常情况不会出现未登录
                return  response(msg(10, __LINE__));
            }
            // 未登录返回 未登录
            // 正常情况不会出现未登录
            return $next($request);
        }
    }
}
