<?php

namespace App\Http\Middleware\Eatest;

use App\Model\Eatest\Evaluation;
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
        $evaluation = Evaluation::query()->find($request->route('id'));
        if(!$evaluation) {
            return response(msg(3, "目标不存在" . __LINE__));
        } else {
            return $next($request);
        }
    }
}
