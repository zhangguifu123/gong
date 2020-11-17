<?php

namespace App\Http\Middleware\jwxt;

use App\Model\jwxt\CountDown;
use Closure;

class OwnerCountDownCheck
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

        $countdown = CountDown::query()->find($request->route('id'));
        if( (session("login") == true && $countdown->uid == $uid) // 发布者本人
            || session("ManagerLogin") == true // 或者管理员
        ) {
            return $next($request);
        } else {
            return response(msg(3, "权限不足或未登录" . __LINE__));
        }
    }
}
