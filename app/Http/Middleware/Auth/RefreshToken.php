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
        // 使用 try 包裹，以捕捉 token 过期所抛出的 TokenExpiredException  异常
        try{
            // 检测用户的登录状态，如果正常则通过
            if (JWTAuth::parseToken()->authenticate()){
                return $next($request);
            }
            throw new UnauthorizedHttpException('jwt-auth', '未登录');
        }catch(TokenExpiredException $exception){
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try{
                //刷新用户token
                $token = $this->auth->refresh();

                $request->headers->set('Authorization','Bearer '.$token);
            }catch (JWTException $exception){
                return msg(4,__LINE__);
            }
        }
        return $next($request);
    }
}
