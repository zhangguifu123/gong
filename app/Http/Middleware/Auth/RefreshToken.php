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
        if (!JWTAuth::parseToken()->check()){
            return response(msg(6,__LINE__));
        }
        return $next($request);
    }
}
