<?php

namespace App\Http\Middleware\Eatest\Reply;

use App\Model\Eatest\EatestReplies;
use Closure;

class OwnerCheck
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
        $user = EatestReplies::query()->find($request->route('id'));
        if(!$user->fromId == session('uid')) {
            return response(msg(11,  __LINE__));
        } else {
            return $next($request);
        }
    }
}
