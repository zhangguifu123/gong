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
        $stu_id = JWTAuth::parseToken()->authenticate()->stu_id;
//        var_dump($stu_id . PHP_EOL);
//        var_dump($request->route('sharingCode'));
        $course = CourseGroup::query()->where('sharingCode',$request->route('sharingCode'))->first();
//            ->get('member')->toArray();
//        var_dump($course->member . PHP_EOL);
//        var_dump(json_decode($course->member, true) . PHP_EOL);
//        return response("失败");
        if( in_array($stu_id, json_decode($course->member, true))) {
            return response(msg(3, "用户已存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
