<?php
namespace App\Http\Middleware;
use Closure;
class EnableCrossRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
//        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';
            $response->header('Access-Control-Allow-Origin', 'localhost:8080');
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
            $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
            $response->header('Access-Control-Allow-Credentials', 'true');
        return $response;
    }
}
