<?php

namespace App\Http\Middleware\Auth;

use Closure;
use http\Env\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;


class RefreshToken extends BaseMiddleware
{

    public function handle($request, Closure $next)
    {
        $newToken = null;
        $auth = JWTAuth::parseToken();
        if (! $token = $auth->setRequest($request)->getToken()) {
            return response(msg(6,__LINE__));
        }
        $user = $auth->authenticate($token);
        if ($user){
            return $next($request);
        }
        if (!$user){
            sleep(rand(1,5)/100);
            $newToken = JWTAuth::refresh($token);
            Redis::setex('token_blacklist:'.$token,30,$newToken);
            return response(msg(13,$newToken));
        }else{
            if($newToken = Redis::get('token_blacklist:'.$token)){
                 // 给当前的请求设置性的token,以备在本次请求中需要调用用户信息
                $request->headers->set('Authorization','Bearer '.$newToken);
                return $next($request);
            }else{
                return response(msg(6,__LINE__));

            }
        }
    }
}
