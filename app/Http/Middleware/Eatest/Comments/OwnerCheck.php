<?php

namespace App\Http\Middleware\Eatest\Comments;

use App\Model\Eatest\EatestComments;
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
        $user = EatestComments::query()->find($request->route('id'));
        if($user->fromId != session('uid') && $user->toId != session('uid')) {
            return response(msg(11, __LINE__.'或文章拥有者不存在'));
        } else {
            return $next($request);
        }
    }
}
