<?php

namespace App\Http\Middleware\Eatest\Reply;

use App\Model\Eatest\EatestComments;
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
        $uid = handleUid($request);
        $user = EatestReplies::query()->find($request->route('id'));
        $comment = EatestComments::query()->find($user->comment_id);
        if($user->fromId != $uid && $user->toId != $uid && $comment->toId != $uid) {
            return response(msg(11,  __LINE__));
        } else {
            return $next($request);
        }
    }
}
