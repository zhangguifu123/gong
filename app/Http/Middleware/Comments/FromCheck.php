<?php

namespace App\Http\Middleware\Comments;

use App\User;
use Closure;

class FromCheck
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
        $user = User::query()->find($request->input('fromId'));
        if(!$user) {
            return response(msg(3, "目标不存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
