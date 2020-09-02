<?php

namespace App\Http\Middleware\Eatest\Comments;

use App\Model\Eatest\EatestComments;
use Closure;

class ExistCheck
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
        if(!$user) {
            return response(msg(3, "目标不存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
