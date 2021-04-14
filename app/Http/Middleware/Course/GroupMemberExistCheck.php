<?php

namespace App\Http\Middleware\Course;

use App\Model\jwxt\CourseGroup;
use Closure;

class GroupMemberExistCheck
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
        $course = CourseGroup::query()->where('id',$request->route('id'))->first();
        if( in_array($stu_id, json_decode($course->member, true))) {
            return $next($request);
        } else {
            return response(msg(3, "用户不存在" . __LINE__));
        }
    }
}
