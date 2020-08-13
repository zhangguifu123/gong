<?php

namespace App\Http\Middleware\Manager;

use Closure;

class RedisTypeCheck
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
        $type = $request->route('type');
        //声明理想数据格式
        $allow_type = ['eatest', 'gulu', 'upick'];

        if (!in_array($type, $allow_type)) {
            return response(msg(3, "type错误" . __LINE__));
        }else{
            return $next($request);
        }
    }
}
