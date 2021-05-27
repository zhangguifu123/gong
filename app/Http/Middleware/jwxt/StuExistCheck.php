<?php

namespace App\Http\Middleware\jwxt;

use Closure;
use http\Env\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Http;

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
