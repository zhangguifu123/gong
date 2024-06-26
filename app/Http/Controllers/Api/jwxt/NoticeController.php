<?php

namespace App\Http\Controllers\Api\jwxt;

use App\Http\Controllers\Controller;
use App\Model\Eatest\CommentLikes;
use App\Model\Eatest\EatestComments;
use App\Model\Eatest\EatestLikes;
use App\Model\Eatest\EatestReplies;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


/**
 * Class NoticeController
 * @package App\Http\Controllers\Api\jwxt
 * @type = {
 *      0 => eatest,
 *      1 => comment
 * }
 */
class NoticeController extends Controller
{
    /**
     * 所有通知
     * @param Request $request
     * @return string
     */
    public function getList (Request$request)
    {
        //评论
        //获取被评论者Id
        $toId = $request->route("id");
        $comment_list = EatestComments::query()
            ->where('eatest_comments.toId','=',$toId)
            ->where('eatest_comments.fromId','!=',$toId)
            ->orderByDesc("eatest_comments.created_at")
            ->leftJoin('evaluations','eatest_comments.eatest_id','=','evaluations.id')
            ->leftJoin('users', 'eatest_comments.fromId', '=', 'users.id')
//            ->get(
//                ['eatest_comments.id','evaluations.title','eatest_id','toId','fromId','fromName','fromAvatar','eatest_comments.content','eatest_comments.created_at as time','evaluations.img']
//            )
            ->get(['eatest_comments.id', 'eatest_comments.fromId', 'eatest_comments.eatest_id as parentId', 'eatest_comments.content', 'eatest_comments.created_at as time', 'users.nickname as fromName', 'users.avatar as fromAvatar', 'evaluations.img', 'evaluations.content as parentContent'])
            ->toArray();
        foreach ($comment_list as $key => $item) {
            $comment_list[$key]['type'] = 0;
        }
        //回复
        $reply_list = EatestReplies::query()
            ->where('eatest_replies.toId','=',$toId)
            ->where('eatest_replies.fromId','!=',$toId)
            ->orderByDesc("eatest_replies.created_at")
            ->leftJoin('eatest_comments','eatest_replies.comment_id','=','eatest_comments.id')
            ->leftJoin('users', 'eatest_replies.fromId', '=', 'users.id')
//            ->get(
//                ['eatest_comments.id','evaluations.title','eatest_id','toId','fromId','fromName','fromAvatar','eatest_comments.content','eatest_comments.created_at as time','evaluations.img']
//            )
//            ->get(['eatest_replies.id', 'fromId', 'eatest_replies.comment_id as parentId', 'eatest_replies.content', 'eatest_replies.created_at as time', 'users.nickname as fromName', 'users.avatar as fromAvatar', 'evaluations.img', 'eatest_comments.content as parentContent'])
            ->get(['eatest_replies.id', 'eatest_replies.fromId', 'eatest_replies.comment_id as parentId', 'eatest_replies.content', 'eatest_replies.created_at as time', 'users.nickname as fromName', 'users.avatar as fromAvatar', 'eatest_comments.content as parentContent'])
            ->toArray();
        foreach ($reply_list as $key => $item) {
            $reply_list[$key]['type'] = 1;
        }
//        $list = $comment_list + $reply_list;
        $list = array_merge($comment_list, $reply_list);
        $list_count = (EatestComments::query()->where('toId','=',$toId)->count()) + (EatestReplies::query()->where('toId','=',$toId)->count());
        $message = ['total'=>$list_count, 'list'=>$list];
        return msg(0, $message);
    }



    /**
     * Eatest Comment 未查看消息
     * @param Request $request
     * @return string
     */
    public function get_eatest_comments_list(Request $request)
    {
        //获取被评论者Id
        $toId = $request->route("id");
        $comment_list = EatestComments::query()
            ->where('toId','=',$toId)
            ->orderByDesc("eatest_comments.created_at")
            ->leftJoin('evaluations','eatest_comments.eatest_id','=','evaluations.id')
            ->leftJoin('users', 'eatest_comments.fromId', '=', 'users.id')
//            ->get(
//                ['eatest_comments.id','evaluations.title','eatest_id','toId','fromId','fromName','fromAvatar','eatest_comments.content','eatest_comments.created_at as time','evaluations.img']
//            )
            ->get(['eatest_comments.id', 'fromId', 'eatest_comments.eatest_id as parentId', 'eatest_comments.content', 'eatest_comments.created_at as time', 'users.nickname as fromName', 'users.avatar as fromAvatar', 'evaluations.img', 'evaluations.content as parentContent'])
            ->toArray();
        foreach ($comment_list as $key => $item) {
            $comment_list[$key]['type'] = 0;
        }

        $list_count = EatestComments::query()->where('toId','=',$toId)->count();
        $message = ['total'=>$list_count,'list'=>$comment_list];
        return msg(0, $message);
    }

    /**
     * Eatest Reply 未查看评论
     * @param Request $request
     * @return string
     */
    public function get_eatest_reply_list(Request $request)
    {
        //获取作者Id
        $toId = $request->route("id");
        $notice_list = EatestReplies::query()
            ->leftJoin('eatest_comments','eatest_replies.comment_id','=','eatest_comments.id')->where('eatest_replies.status',0)
            ->get(
            ['eatest_replies.id','comment_id','eatest_comments.content as commentContent','eatest_replies.toId',
                'eatest_replies.fromId','eatest_replies.fromName','eatest_replies.fromAvatar','eatest_replies.content',
                'eatest_replies.created_at as time','eatest_replies.toId']
        )->toArray();

        $list_count = EatestReplies::query()->where('toId','=',$toId)->count();
        $message = ['total'=>$list_count,'list'=>$notice_list];
        return msg(0, $message);
    }

    public function get_all_comments_replies_list(Request $request)
    {
        //获取作者Id
        $toId = $request->route("id");
        $comment_list = EatestComments::query()->where('toId','=',$toId)
            ->leftJoin('evaluations','eatest_comments.eatest_id','=','evaluations.id')
            ->get(
                ['eatest_comments.id','evaluations.title','eatest_id','toId','fromId','fromName','fromAvatar','eatest_comments.content','eatest_comments.created_at as time','evaluations.img','eatest_comments.status']
            )->toArray();

        $reply_list = EatestReplies::query()
            ->leftJoin('eatest_comments','eatest_replies.comment_id','=','eatest_comments.id')
            ->get(
                ['eatest_replies.id','comment_id','eatest_comments.content as commentContent','eatest_replies.toId',
                    'eatest_replies.fromId','eatest_replies.fromName','eatest_replies.fromAvatar','eatest_replies.content',
                    'eatest_replies.created_at as time','eatest_replies.toId']
            )->toArray();
//        $list_count = EatestComments::query()->where('toId','=',$toId)->count();
        $comment_message = ['total'=>count($comment_list),'list'=>$comment_list];
        $reply_message = ['total'=>count($reply_list),'list'=>$reply_list];
        $message = ['comment_list' => $comment_message,'reply_message' => $reply_message];
        return msg(0, $message);
    }


    /**
     * Eatest Comment 状态修改(指定)
     * @param Request $request
     * @return string
     */
    public function eatest_comment_update(Request $request){

        $comments = EatestComments::query()->find($request->route('id'));
        $status = $comments->update(['status' => 1]);
        if ($status) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    /**
     * Eatest Reply 状态修改(指定)
     * @param Request $request
     * @return string
     */
    public function eatest_reply_update(Request $request){
        //修改
        $comments = EatestReplies::query()->find($request->route('id'));
        $status = $comments->update(['status' => 1]);
        if ($status) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }



    /**
     * Eatest Like 未查看评测点赞
     * @param Request $request
     * @return string
     */
    public function getEatestLikeList (Request $request)
    {
        //提取数据
        $toId = $request->route('id');   //被点赞用户id
        $list = EatestLikes::query()
            ->leftJoin('evaluations', 'evaluations.id','=','eatest_likes.evaluation')
            ->leftJoin('users', 'users.id', '=', 'eatest_likes.user')
//            ->leftJoin([
//                ['evaluations', 'evaluations.id','=','eatest_likes.evaluation'],
//                ['users', 'user.id', '=', 'eatest_likes.user']
//            ])
            ->where([
//                ['users.id', '=', 'eatest_likes.user'],
                ['evaluations.publisher', '=', $toId]
            ])
            ->get(['eatest_likes.id', 'user', 'evaluation', 'users.nickname', 'avatar', 'evaluations.img'])
//            ->get(
//                ['eatest_likes.id','evaluations.title','evaluations.id','fromName','fromAvatar','eatest_likes.user','eatest_likes.evaluation','evaluations.img']
//            )
            ->toArray();
        $message = ['total' => count($list), 'list' => $list];
        return msg(0,$message);
    }





//    /**
//     * Eatest Like 未查看评测点赞
//     * @param Request $request
//     * @return string
//     */
//    public function getEatestLikeList (Request $request)
//    {
//        //提取数据
//        $toId = $request->route('id');   //被点赞用户id
//        $list = EatestLikes::query()
//            ->leftJoin('evaluations', 'evaluations.id','=','eatest_likes.evaluation')
//            ->leftJoin('users', 'users.id', '=', 'eatest_likes.user')
////            ->leftJoin([
////                ['evaluations', 'evaluations.id','=','eatest_likes.evaluation'],
////                ['users', 'user.id', '=', 'eatest_likes.user']
////            ])
//            ->where([
////                ['users.id', '=', 'eatest_likes.user'],
//                ['evaluations.publisher', '=', $toId]
//            ])
//            ->get(['eatest_likes.id', 'user', 'evaluation', 'users.nickname', 'avatar', 'evaluations.img'])
////            ->get(
////                ['eatest_likes.id','evaluations.title','evaluations.id','fromName','fromAvatar','eatest_likes.user','eatest_likes.evaluation','evaluations.img']
////            )
//            ->toArray();
//        $message = ['total' => count($list), 'list' => $list];
//        return msg(0,$message);
//    }

    public function getAllEatestLikeList (Request $request)
    {
        //提取数据
        $toId = $request->route('id');   //用户id
        $list = EatestLikes::query()
            ->leftJoin('evaluations', 'evaluations.id','=','eatest_likes.evaluation')
            ->where([
                ['evaluations.publisher', $toId]
            ])
            ->get(
                ['eatest_likes.id','evaluations.title','evaluations.id','toId','fromId','fromName','fromAvatar','eatest_likes.user','eatest_likes.evaluation','evaluations.img','eatest_likes.status']
            )
            ->toArray();
        $message = ['total' => count($list), 'list' => $list];
        return msg(0,$message);
    }

    /**
     * Eatest Comment Like 未查看评论点赞
     * @param Request $request
     * @return string
     */
    public function getEatestCommentLikeList (Request $request)
    {
        //提取数据
        $toId = $request->route('id');   //用户id
        //拉取未读点赞
        $list = CommentLikes::query()
            ->leftJoin('evaluations', 'evaluations.id','=','comment_likes.evaluation')
            ->where([
                ['comment_likes.status',0],
                ['evaluations.publisher', $toId]
            ])
            ->get(
                ['comment_likes.id','comment_likes.user','comment_likes.evaluation','evaluations.img','evaluations.title','eatest_id','toId','fromId','fromName','fromAvatar']
            )
            ->toArray();
        $message = ['total' => count($list), 'list' => $list];
        return msg(0,$message);
    }

    public function getAllEatestCommentLikeList (Request $request)
    {
        //提取数据
        $toId = $request->route('id');   //用户id
        //拉取未读点赞
        $list = CommentLikes::query()
            ->leftJoin('evaluations', 'evaluations.id','=','comment_likes.evaluation')
            ->where([
                ['evaluations.publisher', $toId]
            ])
            ->get(
                ['comment_likes.id','comment_likes.user','comment_likes.evaluation','evaluations.img','evaluations.title','eatest_id','toId','fromId','fromName','fromAvatar','comment_likes.status']
            )
            ->toArray();
        $message = ['total' => count($list), 'list' => $list];
        return msg(0,$message);
    }

    /**
     * Eatest Like 状态修改(指定)
     * @param Request $request
     * @return string
     */
    public function EatestLikeUpdate(Request $request){
        //修改
        $like = EatestLikes::query()->where('id',$request->route('id'));
        $status = $like->update(['status' => 1]);
        if ($status) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    /**
     * Eatest Comment Like 状态修改(指定)
     * @param Request $request
     * @return string
     */
    public function EatestCommentLikeUpdate(Request $request){
        //修改
        $like = CommentLikes::query()->where('id',$request->route('id'));
        $status = $like->update(['status' => 1]);
        if ($status) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    /**
     * Eatest Comment 状态修改(全部)
     * @param Request $request
     * @return string
     */
    public function EatestCommentAllUpdate(Request $request){

        $comments = EatestComments::query();
        $status = $comments->update(['status' => 1]);
        if ($status) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    /**
     * Eatest Reply 状态修改(全部)
     * @param Request $request
     * @return string
     */
    public function EatestReplyAllUpdate(Request $request){

        $comments = EatestReplies::query();
        $status = $comments->update(['status' => 1]);
        if ($status) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    /**
     * Eatest Like 状态修改(全部)
     * @param Request $request
     * @return string
     */
    public function EatestLikeAllUpdate(Request $request){
        //修改
        $like = EatestLikes::query();
        $status = $like->update(['status' => 1]);
        if ($status) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    /**
     * Eatest Comment Like 状态修改(全部)
     * @param Request $request
     * @return string
     */
    public function EatestCommentLikeAllUpdate(Request $request){
        //修改
        $like = CommentLikes::query();
        $status = $like->update(['status' => 1]);
        if ($status) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }
}
