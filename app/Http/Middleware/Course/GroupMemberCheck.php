<?php

namespace App\Http\Middleware\Course;

use App\Model\jwxt\CourseGroup;
use Closure;

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
        $stu_id = JWTAuth::parseToken()->authenticate()->stu_id;
        $course = CourseGroup::query()->find($request->route('id'));
        if( in_array($stu_id, json_decode($course->member, true))) {
            return response(msg(3, "用户已存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
