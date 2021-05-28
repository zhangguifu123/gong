<?php

namespace App\Http\Middleware\Course;

use App\Model\jwxt\Course;
use App\Model\jwxt\CourseGroup;
use App\User;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class GroupOwnerCheck
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
//        try {
//            $stu_id = JWTAuth::parseToken()->authenticate()->stu_id;
//        } catch (TokenExpiredException $e){
//            $stu_id = 0;
//        }
//        $stu_id = JWTAuth::parseToken()->authenticate()->stu_id;
        $stu_id = handleStuId($request);
        $course = CourseGroup::query()->find($request->route('id'));
        if( $course->FounderUid == $stu_id // 发布者本人
            || session("ManagerLogin") == true // 或者管理员
        ) {
            return $next($request);
        } else {
            return response(msg(3, "权限不足或未登录" . __LINE__));
        }
    }
}
