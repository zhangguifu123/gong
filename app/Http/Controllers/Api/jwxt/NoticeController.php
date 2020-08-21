<?php

namespace App\Http\Controllers\Api\jwxt;

use App\Http\Controllers\Controller;
use App\Model\Eatest\EatestComments;
use App\Model\Eatest\EatestReplies;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    /**
     * Eatest Comment 未查看消息
     * @param Request $request
     * @return string
     */
    public function get_eatest_comments_list(Request $request)
    {
        //获取作者Id
        $toId = $request->route("id");
        $notice_list = EatestComments::query()->where('toId','=',$toId)->where('status','=','0')->get(
            ['id','eatest_id','toId','fromId','fromName','fromAvatar','content','created_at as time']
        )->toArray();

        $list_count = EatestComments::query()->where('toId','=',$toId)->count();
        $message = ['total'=>$list_count,'list'=>$notice_list];
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
        $notice_list = EatestReplies::query()->where('toId','=',$toId)->where('status','=','0')->get(
            ['id','comment_id','toId','fromId','fromName','fromAvatar','content','created_at as time']
        )->toArray();

        $list_count = EatestReplies::query()->where('toId','=',$toId)->count();
        $message = ['total'=>$list_count,'list'=>$notice_list];
        return msg(0, $message);
    }

    /**
     * Eatest Comment 状态修改
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
     * Eatest Reply 状态修改
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

}
