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
    //图片上传
    Route::post('/image','ImageController@upload');
    Route::get('/eatest/list/{page}', "Eatest\EvaluationController@get_list")->where(["page" => "[0-9]+"]);
    /** 用户区 */
//    Route::group(['middleware' => 'login.check'], function () {
        /**Eatest */
        //Eatest增删改查
        Route::post('/eatest','Eatest\EvaluationController@publish');
        Route::get('/eatest/me/{id}', "Eatest\EvaluationController@get")->where(["id" => "[0-9]+"])->middleware("evaluation.exist.check");
            // 测评所有者和管理员均可操作
        Route::group(["middleware" => ['owner.eatest.check', "evaluation.exist.check"]], function () {
            Route::put('/eatest/{id}','Eatest\EvaluationController@update')->where(["id" => "[0-9]+"]);
            Route::delete('/eatest/{id}', "Eatest\EvaluationController@delete")->where(["id" => "[0-9]+"]);
        });
        //Eatest上传图片
        Route::post('/eatest/image', "Eatest\ImageController@upload");
        //Eatest点赞收藏
        Route::group(["middleware" => 'evaluation.exist.check'], function () {
            Route::post('/eatest/like/{id}', "Eatest\LikeController@like")->where(["id" => "[0-9]+"]);
            Route::post('/eatest/keep/{id}', "Eatest\CollectionController@keep")->where(["id" => "[0-9]+"]);
        });

        //Eatest评论
        Route::post('eatest/{id}/comments','Eatest\CommentController@publish')->where(["id"=>"[0-9]+"])->middleware(['evaluation.exist.check','comment.from.check']);
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

        //测试
        Route::get('/eatest/image', "Eatest\ImageController@get");


//    });
});
