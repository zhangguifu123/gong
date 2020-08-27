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

    /** 公共区 */
    Route::post('login','StudentLoginController@login');
    Route::post('/manager/login', "ManagerController@login");
    Route::get('/upick/list/{page}', "Eatest\FoodController@get_list")->where(["page" => "[0-9]+"]);
    Route::get('/eatest/{id}', "Eatest\EvaluationController@get")->where(["id" => "[0-9]+"])->middleware("eatest.exist.check");
    //图片上传
    Route::post('/image','ImageController@upload');
    Route::get('/eatest/list/{page}', "Eatest\EvaluationController@get_list")->where(["page" => "[0-9]+"]);


    /**Upick 管理员*/
    //用户登录验证
    Route::get('/food', "Eatest\FoodController@get");
    //管理员登录验证区
    Route::group(['middleware' => 'manager.login.check'], function () {
        Route::post('/manager/update', "ManagerController@update");
        Route::get('/manager/list', "ManagerController@list");

        // 超级管理员验证
        Route::group(['middleware' => 'manager.super.check'], function () {
            Route::post('/manager/add', "ManagerController@add");
            Route::delete('/manager/{id}', "ManagerController@delete")->where(["id" => "[0-9]+"]);
        });

        // 美食库区域
        Route::post('/upick', "Eatest\FoodController@publish");

    });
    /**
     * 测试Upick暂时移出来
     */
    Route::group(['middleware' => 'food.exist.check'], function () {
        Route::put('/upick/{id}', "Eatest\FoodController@update")->where(["id" => "[0-9]+"]);
        Route::delete('/upick/{id}', "Eatest\FoodController@delete")->where(["id" => "[0-9]+"]);
    });

    /** 用户区 */
    Route::group(['middleware' => 'login.check'], function () {
        /**CountDown倒计时 */
        Route::post('/jwxt/countdown', 'jwxt\CountDownController@addCountDown');

        Route::group(["middleware" => ["owner.countdown.check","countdown.exist.check"]],function (){
            Route::delete('/jwxt/countdown/{id}', 'jwxt\CountDownController@delete')->where(["id" => "[0-9]+"]);
            Route::put('/jwxt/countdown/{id}', 'jwxt\CountDownController@update')->where(["id" => "[0-9]+"]);
        });

        Route::get('/jwxt/countdown/{uid}', 'jwxt\CountDownController@query')->where(["uid" => "[0-9]+"])->middleware("owner.check");


        /**Eatest */
        //Eatest增删改查
        Route::post('/eatest','Eatest\EvaluationController@publish');
        Route::get('/eatest/me/{id}', "Eatest\EvaluationController@get")->where(["id" => "[0-9]+"])->middleware("owner.check");
            // 测评所有者和管理员均可操作
        Route::group(["middleware" => ['owner.eatest.check', "eatest.exist.check"]], function () {
            Route::put('/eatest/{id}','Eatest\EvaluationController@update')->where(["id" => "[0-9]+"]);
            Route::delete('/eatest/{id}', "Eatest\EvaluationController@delete")->where(["id" => "[0-9]+"]);
        });
        //Eatest上传图片
        Route::post('/eatest/image', "Eatest\ImageController@upload");
        //Eatest点赞收藏
        Route::group(["middleware" => 'eatest.exist.check'], function () {
            Route::post('/eatest/like/{id}', "Eatest\LikeController@like")->where(["id" => "[0-9]+"]);
            Route::post('/eatest/keep/{id}', "Eatest\CollectionController@keep")->where(["id" => "[0-9]+"]);
        });

        //Eatest评论
        Route::post('eatest/{id}/comments','Eatest\CommentController@publish')->where(["id"=>"[0-9]+"])->middleware(['eatest.exist.check','comment.from.check']);
        //Eatest评论回复
        Route::post('eatest/{toId}/reply/{fromId}','Eatest\ReplyController@publish')->where(["toId"=>"[0-9]+","fromId"=>"[0-9]+"])->middleware('reply.exist.check');

        /**User */
        Route::group(["middleware" => ['owner.check']], function () {
        //获取收藏列表
        Route::get('/user/{uid}/keep', "Eatest\CollectionController@get_user_collection_list")->where(["uid" => "[0-9]+"]);
        //获取我的Eatest列表
        Route::get('/user/{uid}/publish', "UserLoginController@get_user_publish_list")->where(["uid" => "[0-9]+"]);
        });

        /**Notice */
        Route::group(["middleware" => ['user.exist.check']],function (){
            Route::get('/notice/eatest/comments/{id}',"jwxt\NoticeController@get_eatest_comments_list")->where(["id" => "[0-9]+"]);
            Route::get('/notice/eatest/reply/{id}',"jwxt\NoticeController@get_eatest_reply_list")->where(["id" => "[0-9]+"]);
        });

            Route::put('/notice/eatest/comments/{id}',"jwxt\NoticeController@eatest_comment_update")->where(["id" => "[0-9]+"])->middleware('comment.exist.check');
            Route::put('/notice/eatest/reply/{id}',"jwxt\NoticeController@eatest_reply_update")->where(["id" => "[0-9]+"])->middleware('reply.id.check');

        /**AssociationCode */
        Route::get('/course/association/{uid}','jwxt\AssociationCodeController@get_association')->where(["uid"=>"[0-9]{12}"]);
        Route::get('/course/uid/{association}','jwxt\AssociationCodeController@get_uid')->where(["association" => "\w{8}"]);

        //测试
        Route::get('/eatest/image', "Eatest\ImageController@get");


    });
});
