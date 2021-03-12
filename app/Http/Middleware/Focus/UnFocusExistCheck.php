<?php

namespace App\Http\Middleware\Focus;

use App\Model\Eatest\FocusOn;
use App\User;
use Closure;

class UnFocusExistCheck
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

        $unFollowId = $request->input('unFollowId');
        //检查unFollowId是否存在
        $user = User::query()->find($unFollowId);
        if ($user == null){
            return response(msg(11,__LINE__));
        }
        //检查是否关注
        $check = FocusOn::query()->where('uid',$uid)->where('unFollowId',$unFollowId);
        if($check) {
            return $next($request);
        } else {
            return response(msg(11,__LINE__));
        }
    }
}
