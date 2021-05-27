<?php

namespace App\Http\Middleware\jwxt;

use Closure;

class StuExistCheck
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
        //获取当前用户学号
        $sid = JWTAuth::parseToken()->authenticate()->stu_id;
        $response = json_decode(Http::get('https://jwxt.sky31.com/api/student/' . $sid . '/info')->body(),true);
        if($response['code'] == 0) {
            return $next($request);
        }else{
            return response(msg(6, __LINE__));
        }
    }
}
