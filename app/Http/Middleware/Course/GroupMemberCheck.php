<?php

namespace App\Http\Middleware\Course;

use App\Model\jwxt\CourseGroup;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class GroupMemberCheck
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
        $stu_id = $request->route('uid');
        $course = CourseGroup::query()->where('sharingCode',$request->route('sharingCode'))->first();
        if( in_array($stu_id, json_decode($course->member, true))) {
            return response(msg(3, "用户已存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
