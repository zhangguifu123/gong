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
            //Eatest增删改查
            Route::post('/eatest','Eatest\EvaluationController@publish');
            Route::get('/eatest/{id}', "Eatest\EvaluationController@get")->where(["id" => "[0-9]+"])->middleware("evaluation.exist.check");
            Route::get('/eatest/list/{page}', "Eatest\EvaluationController@get_list")->where(["page" => "[0-9]+"]);
                // 测评所有者和管理员均可操作
            Route::group(["middleware" => ['owner.check', "evaluation.exist.check"]], function () {
                Route::put('/eatest/{id}','Eatest\EvaluationController@update')->where(["id" => "[0-9]+"]);
                Route::delete('/eatest/{id}', "Eatest\EvaluationController@delete")->where(["id" => "[0-9]+"]);
            });
            //Eatest上传图片
            Route::post('/eatest/image', "Eatest\ImageController@upload");
            //Eatest点赞收藏
            Route::group(["middleware" => 'evaluation.exist.check'], function () {
                Route::post('/eatest/like/{id}', "Eatest\LikeController@mark")->where(["id" => "[0-9]+"]);
                Route::post('/eatest/keep/{id}', "Eatest\CollectionController@keep")->where(["id" => "[0-9]+"]);
            });

            //获取收藏列表
            Route::get('/user/{uid}/keep', "Eatest\CollectionController@get_user_collection_list")->where(["uid" => "[0-9]+"]);
            //获取我的Eatest列表
            Route::get('/user/{uid}/publish', "UserLoginController@get_user_publish_list")->where(["uid" => "[0-9]+"]);
            //测试
            Route::get('/eatest/image', "Eatest\ImageController@get");
            Route::get('/foodchannel/list/{page}', "FoodChannelController@get_list")->where(["page" => "[0-9]+"]);


    });
});
