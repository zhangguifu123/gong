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

    //编码
    Route::post('/code','StudentLoginController@code');
    //解码
    Route::post('/decode','StudentLoginController@decode');

    Route::post('/login','StudentLoginController@login')->middleware("stu.exist.check");
    Route::post('/manager/login', "ManagerController@login");
    //个人信息
    Route::get('/manager/user/{id}','StudentLoginController@userMsg')->where(["id" => "[0-9]+"]);
    //图片上传
    Route::post('/image','ImageController@upload');
    //Eatest拉取单页详情
    Route::get('/eatest/{id}', "Eatest\EvaluationController@get")->where(["id" => "[0-9]+"])->middleware("eatest.exist.check");
    //Eatest 帖子、评论、回复列表
    Route::get('/eatest/list/{page}', "Eatest\EvaluationController@get_list")->where(["page" => "[0-9]+"]);
    Route::get('/eatest/{id}/comments','Eatest\CommentController@get_list')->where(["id"=>"[0-9]+"])->middleware(['eatest.exist.check']);
    Route::get('/eatest/comment/{id}/reply','Eatest\ReplyController@get_list')->where(["id"=>"[0-9]+"])->middleware(['comment.exist.check']);

    //模糊搜索
    Route::get('/eatest/fuzzySearch/{index}/{page}','Eatest\searchEatestController@search')->where(['page' => '[0-9]+']);
    //添加标签
    Route::post('manager/label','Manager\LabelController@addLabel');

    /** 后台 */
    //定时删除垃圾图片 **
//    Route::post('/eatest/image/delete','ImageController@delete');
    //测试
//    Route::get('/image', "ImageController@get");

    /**管理员*/
//    用户登录验证
//    Route::get('/food', "Eatest\FoodController@get");
    //管理员登录验证区
    Route::group(['middleware' => 'manager.login.check'], function () {
        //超级管理员验证
        Route::group(['middleware' => 'manager.super.check'], function () {
            /** manager */
            Route::post('/manager', "ManagerController@add");
            Route::put('/manager/{id}', "ManagerController@updateMsg")->where(["id" => "[0-9]+"]);
            Route::delete('/manager/{id}', "ManagerController@delete")->where(["id" => "[0-9]+"]);
            Route::get('/manager/list', "ManagerController@getList");
            /** user */
            Route::get('/manager/user/list/{page}','Manager\UserController@showUser')->where(["page" => "[0-9]+"]);
            Route::put('/manager/user/{id}/status','Manager\UserController@updateStatus')->where(["id" => "[0-9]+"]);
            Route::get('/manager/user/{index}/{page}/{status}','Manager\UserController@searchUser')->where(["page" => "[0-9]+", "status" => "[0-1]"]);
            /** EatestReview */
            Route::get('manager/eatestReview/list/{page}/evaluation','Manager\ReviewController@getEvaluationList')->where(["page" => "[0-9]+"]);
            Route::get('manager/eatestReview/list/{page}/comment','Manager\ReviewController@getCommentList')->where(["page" => "[0-9]+"]);
            Route::put('manager/eatestReview/{id}/evaluationStatus','Manager\ReviewController@updateEvaluationStatus')->where(["id" => "[0-9]+"]);
            Route::put('manager/eatestReview/{id}/commentStatus','Manager\ReviewController@updateCommentStatus')->where(["id" => "[0-9]+"]);
            /** EatestReport */
            Route::put('/manager/report/{id}/status',"Manager\ReportController@handleReport")->where(["id" => "[0-9]+"]);
            Route::get('/manager/report/list/{page}/{status}', "Manager\ReportController@showReport")->where(["page" => "[0-9]+", "status" => "[0-9]+"]);
            /** EatestAppeal */
            Route::put('/manager/appeal/{id}/status',"Manager\AppealController@handleAppeal")->where(["id" => "[0-9]+"]);
            Route::get('/manager/appeal/list/{page}/{status}', "Manager\AppealController@showAppeal")->where(["page" => "[0-9]+", "status" => "[0-9]+"]);
            /** EatestTopic */
            Route::post('/manager/topic','Manager\TopicController@addTopic');
            Route::delete('manager/topic/{id}','Manager\TopicController@dropTopic')->where(["id" => "[0-9]+"]);
            Route::put('manager/topic/{id}/topOrder','Manager\TopicController@topOrder')->where(["id" => "[0-9]+"]);;
            /** EatestLabel */
//            Route::post('manager/label','Manager\LabelController@addLabel');
            Route::delete('manager/label/{id}','Manager\LabelController@dropLabel')->where(["id" => "[0-9]+"]);
            //模糊搜索
            Route::get('eatest/fuzzySearch/{index}/{topic}/{orderBy}/{page}','Eatest\searchEatestController@cdSearch')->where(['page' => '[0-9]+', 'orderBy' => '[a-zA-Z]+']);


//            /** Eatest */
            Route::get('/eatest/{uid}/{page}','Eatest\EvaluationController@getOneList')->where(["uid" => "[0-9]+", "page" => "[0-9]+"]);
            Route::get('/eatest/comment/{uid}/{page}','Eatest\CommentController@getOneList')->where(["uid" => "[0-9]+", "page" => "[0-9]+"]);
            Route::get('/eatest/reply/{uid}/{page}','Eatest\ReplyController@getOneList')->where(["uid" => "[0-9]+", "page" => "[0-9]+"]);
//            Route::put('/manager/eatest/{id}/ban', "Eatest\EvaluationController@updateStatus")->where(["id" => "[0-9]+"]);

            /** Upick  */
            Route::group(['middleware' => 'food.exist.check'], function () {
                Route::put('/upick/{id}', "Eatest\FoodController@update")->where(["id" => "[0-9]+"]);
                Route::delete('/upick/{id}', "Eatest\FoodController@delete")->where(["id" => "[0-9]+"]);
            });

            // 美食库区域
            Route::post('/upick', "Eatest\FoodController@publish");
        });


        //专栏权限
        Route::group(['middleware' => 'manager.special.check'],function (){
            /** EatestSpecialColumn */
            Route::post('manager/specialColumn','Manager\SpecialColumnController@add');
            Route::get('manager/specialColumn','Manager\SpecialColumnController@getList');
            Route::get('manager/specialColumn/{id}','Manager\SpecialColumnController@getListOne')->where(["id" => "[0-9]+"]);
            Route::delete('manager/specialColumn{id}','Manager\SpecialColumnController@delete')->where(["id" => "[0-9]+"]);
        });
    });


    /** 推送 */
    Route::post('push/send',"PushSdk\ToSingleController@send");

    /** 用户区 */

    Route::group(['middleware' => ['auth.check']], function () {
        //审核
        Route::post('/review/sensitiveWord',"Manager\ReviewController@sensitiveFilter");
        Route::post('/review/eatest',"Manager\ReviewController@eatestFilter");
        Route::post('/review/eatest/comment',"Manager\ReviewController@commentFilter");
        Route::post('/review/eatest/comment/reply',"Manager\ReviewController@replyFilter");
        //个人信息
        Route::get('/user/{id}','StudentLoginController@userMsg')->where(["id" => "[0-9]+"])->middleware(['stu.exist.check']);
        //退出登录
        Route::post('/logout','StudentLoginController@logout');
        /** 关注 */
        Route::post('focus',"Eatest\FocusController@focus")->middleware(['focus.exist.check']);
        Route::post('unfocus',"Eatest\FocusController@unfocus")->middleware(['unfocus.exist.check']);
        Route::get('focus/{type}/{uid}',"Eatest\FocusController@get_user_focus_list");
        /** 推送 */
        Route::post('push/toSingle',"PushSdk\ToSingleController@pushMessage");
//        /** Tip */
//        Route::post('/tip',"Manager\TipController@upload");
//
//        /** Appeal */
//        Route::post('/appeal',"Manager\AppealController@upload");

        /** Report */
        Route::post('/report',"Manager\ReportController@addReport");
        /** Appeal */
        Route::post('/appeal',"Manager\AppealController@addAppeal");

        /** EatestLabel */
        Route::get('/label/list',"Manager\LabelController@showLabel");
        /** EatestTopic */
        Route::get('/topic/list/{page}',"Manager\TopicController@showTopic");

        /** Ecard*/
        Route::post('/ecard/binding',"Ecard\ConsumeController@binding");
        Route::get('/ecard/{stu_id}',"Ecard\ConsumeController@get");
        Route::post('/library/binding',"Ecard\LibraryController@binding");
        /** Upick */
        Route::post('/upick/keep/{id}', "Eatest\CollectionController@upick_keep")->where(["id" => "[0-9]+"])->middleware(['food.exist.check']);
        Route::get('/upick/me/{uid}',"Eatest\FoodController@get_upick_list")->where(["uid" => "[0-9]+"])->middleware(['owner.check']);
        /**头像上传 */
        Route::post('/avatar','AvatarImageController@upload');
        Route::put('/nickname','StudentLoginController@update_nickname');
        /**CountDown倒计时 */
        Route::post('/countdown', 'jwxt\CountDownController@addCountDown');
        Route::group(["middleware" => ["owner.countdown.check","countdown.exist.check"]],function (){
            Route::delete('/countdown/{id}', 'jwxt\CountDownController@delete')->where(["id" => "[0-9]+"]);
            Route::put('/countdown/{id}', 'jwxt\CountDownController@update')->where(["id" => "[0-9]+"]);
            Route::put('/countdown/top/{id}','jwxt\CountDownController@top')->where(["id" => "[0-9]+"]);
        });

        Route::get('/countdown/{uid}', 'jwxt\CountDownController@query')->where(["uid" => "[0-9]+"])->middleware(['stu.exist.check']);
//            ->middleware("owner.check");

        /** 多人空课表 */
        Route::post('/CourseGroup/{uid}','jwxt\CourseGroupController@addGroup')->where(['uid' => '[0-9]+']);
        Route::get('/CourseGroup/{sharingCode}','jwxt\CourseGroupController@getOneGroup')->where(['sharingCode' => '[A-Z0-9]+']);
//        Route::get('/CourseGroup/list/{Founder}/created','jwxt\CourseGroupController@getCreatedGroupList')->where(['Founder' => '[0-9]+']);
        Route::get('/CourseGroup/list/{uid}/created','jwxt\CourseGroupController@getCreatedGroupList')->where(['id' => '[0-9]+']);
        Route::get('/CourseGroup/list/{uid}/joined','jwxt\CourseGroupController@getJoinedGroupList')->where(['uid' => '[0-9]+']);
        Route::delete('/CourseGroup/{id}','jwxt\CourseGroupController@deleteGroup')->where(['id' => '[0-9]+'])->middleware(['group.exist.check','group.owner.check']);
        Route::put('/CourseGroup/{id}','jwxt\CourseGroupController@updateGroup')->where(['id' => '[0-9]+'])->middleware(['group.exist.check','group.owner.check']);
        Route::post('/CourseGroup/{sharingCode}/member/{uid}','jwxt\CourseGroupController@joinGroup')->where(['sharingCode' => '[A-Z0-9]+','uid' => '[0-9]+'])->middleware(['group.member.check']);      //
        Route::delete('/CourseGroup/{id}/member/{uid}','jwxt\CourseGroupController@deleteGroupMember')->where(['id' => '[0-9]+' , 'uid' => '[0-9]+'])->middleware(['group.exist.check','group.owner.check']);
        Route::get('/CourseGroup/sharingCode','jwxt\CourseGroupController@createSharingCode');
        Route::get('/CourseGroup/{id}/member/list','jwxt\CourseGroupController@getMemberList')->where(['id' => '[0-9]+']);
        Route::post('/CourseGroup/{id}/emptyCourse','jwxt\CourseController@createEmptyCourse')->where(['id' => '[0-9]+']);


        /** Course*/
        Route::post('/course/extra',"jwxt\CourseController@publish");
        //测评所有者和管理员均可操作
//        Route::get('/course/extra/{uid}',"jwxt\CourseController@get_list")->middleware("owner.check");
        Route::get('/course/extra/{uid}',"jwxt\CourseController@get_list")->middleware(['stu.exist.check']);
//        Route::group(["middleware" => ['owner.course.check']], function (){
        Route::put('/course/extra/{id}',"jwxt\CourseController@update")->where(['id' => '[0-9a-zA-Z]+']);
        Route::delete('/course/extra/{id}',"jwxt\CourseController@delete");
//        });
        /**Eatest */
        Route::post('/test','Eatest\EvaluationController@test');
        //Eatest增删改查
        Route::post('/eatest','Eatest\EvaluationController@publish');

        Route::get('/eatest/me/{uid}','Eatest\EvaluationController@get_me_list');
        Route::get('/eatest/like/{uid}','Eatest\EvaluationController@get_like_list');
        Route::get('/eatest/collection/{uid}','Eatest\EvaluationController@get_collection_list');

        // 测评所有者和管理员均可操作
        Route::group(["middleware" => ["eatest.exist.check",'owner.eatest.check']], function () {

            Route::put('/eatest/{id}','Eatest\EvaluationController@update')->where(["id" => "[0-9]+"]);
            Route::delete('/eatest/{id}', "Eatest\EvaluationController@delete")->where(["id" => "[0-9]+"]);
        });
//        Eatest上传图片 **作废**
//        Route::post('/eatest/image', "Eatest\ImageController@upload");

        Route::group(["middleware" => 'eatest.exist.check'], function () {
            //Eatest点赞收藏
            Route::post('/eatest/like/{id}', "Eatest\LikeController@like")->where(["id" => "[0-9]+"]);
            Route::post('/eatest/keep/{id}', "Eatest\CollectionController@eatest_keep")->where(["id" => "[0-9]+"]);
        });

        //Eatest评论
        Route::post('eatest/comments/like/{id}','Eatest\CommentController@like')->where(["id"=>"[0-9]+"])->middleware(['comment.exist.check']);
        Route::post('eatest/{id}/comments','Eatest\CommentController@publish')->where(["id"=>"[0-9]+"])->middleware(['eatest.exist.check','comment.from.check']);

        Route::delete('eatest/comments/{id}','Eatest\CommentController@delete')->where(["id"=>"[0-9]+"])->middleware(['comment.exist.check','comment.owner.check']);
        //Eatest评论回复
//        Route::post('eatest/{toId}/reply/{fromId}','Eatest\ReplyController@publish')->where(["toId"=>"[0-9]+","fromId"=>"[0-9]+"])->middleware('reply.tofrom.check');
        Route::post('/eatest/comment/{id}/reply','Eatest\ReplyController@publish')->where(["id"=>"[0-9]+"])->middleware('reply.tofrom.check');

        Route::delete('/eatest/reply/{id}','Eatest\ReplyController@delete')->where(["id"=>"[0-9]+"])->middleware(['reply.exist.check','reply.owner.check']);
        /**User */
        Route::group(["middleware" => ['owner.check']], function () {
            //获取收藏列表
            Route::get('/user/{uid}/keep', "Eatest\CollectionController@get_user_collection_list")->where(["uid" => "[0-9]+"]);
            //获取我的Eatest列表
            Route::get('/user/{uid}/publish', "UserLoginController@get_user_publish_list")->where(["uid" => "[0-9]+"]);
        });

        /**Notice */
        Route::group(["middleware" => ['user.exist.check']],function (){
            Route::get('/notice/eatest/{id}',"jwxt\NoticeController@getList")->where(["id" => "[0-9]+"]);
            Route::get('/notice/eatest/comments/{id}',"jwxt\NoticeController@get_eatest_comments_list")->where(["id" => "[0-9]+"]);
            Route::get('/notice/eatest/reply/{id}',"jwxt\NoticeController@get_eatest_reply_list")->where(["id" => "[0-9]+"]);
            Route::get('/notice/eatest/like/{id}',"jwxt\NoticeController@getEatestLikeList")->where(["id" => "[0-9]+"]);
            Route::get('/notice/eatest/comment/like/{id}',"jwxt\NoticeController@getEatestCommentLikeList")->where(["id" => "[0-9]+"]);
            Route::get('/notice/allEatestComment/like/{id}',"jwxt\NoticeController@get_all_comments_replies_list")->where(["id" => "[0-9]+"]);
            Route::get('/notice/getAllEatestLikeList/like/{id}',"jwxt\NoticeController@getAllEatestLikeList")->where(["id" => "[0-9]+"]);
            Route::get('/notice/getAllEatestCommentLikeList/like/{id}',"jwxt\NoticeController@getAllEatestCommentLikeList")->where(["id" => "[0-9]+"]);
        });

        Route::put('/notice/eatest/comments/{id}',"jwxt\NoticeController@eatest_comment_update")->where(["id" => "[0-9]+"])->middleware('comment.exist.check');
        Route::put('/notice/eatest/reply/{id}',"jwxt\NoticeController@eatest_reply_update")->where(["id" => "[0-9]+"])->middleware('reply.exist.check');
        Route::put('/notice/eatest/like/{id}',"jwxt\NoticeController@EatestLikeUpdate")->where(["id" => "[0-9]+"]);
        Route::put('/notice/eatest/comment/like/{id}',"jwxt\NoticeController@EatestCommentLikeUpdate")->where(["id" => "[0-9]+"]);

        Route::put('/notice/eatest/comment',"jwxt\NoticeController@EatestCommentAllUpdate");
        Route::put('/notice/eatest/reply',"jwxt\NoticeController@EatestReplyAllUpdate");
        Route::put('/notice/eatest/like',"jwxt\NoticeController@EatestLikeAllUpdate");
        Route::put('/notice/eatest/comment/like',"jwxt\NoticeController@EatestCommentLikeAllUpdate");

        /**AssociationCode */
        //获取关联码
        Route::get('/course/association/{uid}','jwxt\AssociationCodeController@get_association')->where(["uid"=>"[0-9]{12}"]);
        //获取学号
        Route::get('/course/uid/{association}','jwxt\AssociationCodeController@get_uid')->where(["association" => "\w{8}"]);
        //关联课表、空课表
        Route::post('/course/empty','jwxt\CourseController@empty_course');
        Route::post('/course/associate','jwxt\CourseController@associate_course');
        Route::post('/course/info','jwxt\CourseController@info');

        //用户反馈
        Route::post('/user/feedback','User\UserFeedbackController@addFeedback');

        /** 优惠券 */
        //存储优惠劵
        Route::post('/coupon/save','Coupon\SaveCouponController@saveCoupon');
        //修改优惠劵
        Route::put('/coupon/update','Coupon\SaveCouponController@updateCoupon');
        //删除优惠劵
        Route::delete('/coupon/delete','Coupon\SaveCouponController@deleteCoupon');
        //获取优惠劵
        Route::get('/coupon/get','Coupon\GetCouponController@getCoupon');
        //获取商家列表
        Route::get('/coupon/getStore','Coupon\GetCouponController@getStore');
        //使用优惠劵
        Route::post('/coupon/use','Coupon\GetCouponController@useCoupon');
        //添加商家
        Route::post('/coupon/addStore','Coupon\StoreController@addStore');
        //修改商家
        Route::post('/coupon/updateStore','Coupon\StoreController@updateStore');



    });



});
