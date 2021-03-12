<?php

namespace App\Http\Middleware\Focus;

use App\Model\Eatest\FocusOn;
use App\User;
use Closure;

class FocusExistCheck
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

        $followId = $request->input('follow_uid');
        //检查follow_uid是否存在
        $user = User::query()->find($followId);
        if ($user == null){
            return response(msg(11,__LINE__));
        }
        //检查是否关注
        $check = FocusOn::query()->where('uid',$uid)->where('follow_uid',$followId)->get()->toArray();
        if($check == null) {
            return $next($request);
        } else {
            return response(msg(8,__LINE__));
        }
    }
}
