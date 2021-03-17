<?php

namespace App\Http\Middleware;

use Closure;

class SpecialPowerCheck
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
        if(session()->has('ManagerLogin') && session('level') === 1) {
            return $next($request);
        } else {
            return response(msg(3, "权限不足" .__LINE__), 200);
        }
    }
}
