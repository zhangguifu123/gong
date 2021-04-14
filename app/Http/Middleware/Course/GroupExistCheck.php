<?php

namespace App\Http\Middleware\Course;

use App\Model\jwxt\Course;
use App\Model\jwxt\CourseGroup;
use Closure;

class GroupExistCheck
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
        $course = CourseGroup::query()->find($request->route('id'));
        if(!$course) {
            return response(msg(3, "目标不存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
