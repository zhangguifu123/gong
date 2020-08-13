<?php

namespace App\Http\Middleware\Manager;

use App\User;
use Closure;

class ReplyCheck
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
        $commenter = User::query()->find($request->route('fromId'));
        $replyer = User::query()->find($request->route('toId'));
        if(!$commenter || !$replyer) {
            return response(msg(3, "目标不存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
