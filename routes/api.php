<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::namespace('Api')->group(function (){
    Route::post('login','StudentLoginController@login');


    Route::group(['middleware' => 'login.check'], function () {
            Route::post('/eatest','Eatest\EvaluationController@publish');
            Route::get('/eatest/{id}', "Eatest\EvaluationController@get")->where(["id" => "[0-9]+"])->middleware("evaluation.exist.check");
            Route::get('/eatest/list/{page}', "Eatest\EvaluationController@get_list")->where(["page" => "[0-9]+"]);
            Route::post('/eatest/image', "Eatest\ImageController@upload");
            // 测评所有者和管理员均可操作
            Route::group(["middleware" => ['owner.check', "evaluation.exist.check"]], function () {
                Route::put('/eatest/{id}','Eatest\EvaluationController@update')->where(["id" => "[0-9]+"]);
                Route::delete('/eatest/{id}', "Eatest\EvaluationController@delete")->where(["id" => "[0-9]+"]);
            });


            Route::get('/eatest/image', "Eatest\ImageController@get");
            Route::get('/activity/top', "ActivityController@get_top");
            Route::get('/foodchannel/list/{page}', "FoodChannelController@get_list")->where(["page" => "[0-9]+"]);


    });
});
