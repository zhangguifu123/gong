<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /** 刷新token */
    public function refresh()
    {
        if (!JWTAuth::parseToken()->check()){
            return msg(0,$this->respondWithToken(JWTAuth::parseToken()->refresh()));
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            return msg(14,$user);
        }
    }

    /**
 * Get the token array structure.
 *

     * @param  string $token
 *

     * @return array
     */

    protected function respondWithToken($token)

    {

        return [

            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => JWTAuth::parseToken()->authenticate()
        ];

    }
}
