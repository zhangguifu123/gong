<?php

namespace App\Http\Middleware\Course;

use App\Model\jwxt\Course;
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
        print_r($uid);
        $course = Course::query()->find($request->route('id'));
        print_r($course->uid);
        if( $course->uid == $uid // 发布者本人
            || session("ManagerLogin") == true // 或者管理员
        ) {
            return $next($request);
        } else {
            return response(msg(3, "权限不足或未登录" . __LINE__));
        }
    }
}
