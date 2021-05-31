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
use \Redis;

class RefreshToken extends BaseMiddleware
{

    public function handle($request, Closure $next)
    {
//        if (session()->has('ManagerLogin') && session('ManagerLogin') === true) {
//            return $next($request);
//        }
        //检查请求是否带有token
        $authorization = $request->header('Authorization');
        if(!((isset($authorization) && $authorization !=null)||(session()->has('ManagerLogin') && session('ManagerLogin') === true))) {
            return response(msg(6, __LINE__));
        }
        //检查token状态
        $newToken = null;
        $auth = JWTAuth::parseToken();
        if (! $token = $auth->setRequest($request)->getToken()) {
            return response(msg(6,__LINE__));
        }
        $user = $auth->check();
        if ($user){
            return $next($request);
        }
        if (!$user){
            $redis = new Redis();
            $redis->connect("gong_redis", 6379);
            if($newToken = $redis->get('token_blacklist:'.$token)){
                // 给当前的请求设置性的token,以备在本次请求中需要调用用户信息
                $request->headers->set('Authorization','Bearer '.$newToken);
                return $next($request);
            }else{
                sleep(rand(1,5)/100);
                $newToken = JWTAuth::refresh($token);
                $redis->setex('token_blacklist:'.$token,30,$newToken);
                return response(msg(13,["token"=>$newToken,"expires_in"=>JWTAuth::factory()->getTTL() * 60]));
            }

        }
    }
}
