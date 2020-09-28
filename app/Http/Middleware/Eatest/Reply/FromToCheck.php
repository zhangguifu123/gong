<?php

namespace App\Http\Middleware\Eatest\Reply;

use App\User;
use Closure;

class FromToCheck
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
        $commenter = User::query()->find($request->input('fromId'));
        $replyer = User::query()->find($request->input('toId'));
        
	if(!$commenter || !$replyer) {
	
            return response(msg(3, "评论or被评论者不存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
