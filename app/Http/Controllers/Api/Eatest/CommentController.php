<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
use App\Model\Eatest\EatestReplies;
use App\Model\Eatest\Evaluation;
use App\User;
use App\Model\Eatest\EatestComments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    //评测评论
    public function publish(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        $eatest_id = $request->route('id');
        $data = $data + ["eatest_id"=>$eatest_id,"status" => 0];
        $comments = new EatestComments($data);

        if ($comments->save()) {
            return msg(0,$comments->id);
        }
        //未知错误
        return msg(4, __LINE__);
    }

    //获取美文评论
    public function get_list(Request $request){
        $all_list = [];
        $comment_list = EatestComments::query()->
        where('eatest_id','=',$request->route('id'))
            ->leftJoin('users','eatest_comments.fromId','=','users.id')
            ->get([
            'eatest_comments.id','toId','fromId','users.nickname as fromName','users.avatar as fromAvatar','content','eatest_comments.created_at as time'
        ])->toArray();
        foreach ($comment_list as $i){
            $reply_list = EatestReplies::query()
                ->where('comment_id','=',$i['id'])
                ->leftJoin('users','eatest_replies.fromId','=','users.id')
                ->get([
                    'eatest_replies.id','fromId','users.nickname as fromName','toId','comment_id','users.avatar as fromAvatar','content','eatest_replies.created_at as time'
                ])->toArray();
            $all_list[] = $i + ['reply'=>$reply_list];
        }

        $message = ['total' => count($comment_list), 'list' => $all_list];
        return msg(0, $message);
    }

    //删除
    public function delete(Request $request)
    {

        $comments = EatestComments::query()->find($request->route('id'));
        // 将该评测从我的发布中删除

        $toId = $comments->id;
        $reply = EatestReplies::query()->find($toId);
        if ($reply){
            $reply->delete();
        }
        $comments->delete();
        $data = ['删除comment_id' => $toId];
        return msg(0, $data);
    }

    //检查函数
    private function data_handle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "fromId" => ["integer"],
            "fromName" => ["string"],
            "fromAvatar" => ["json"],
            "content" => ["string", "max:50"]
        ];
        //是否缺失参数
        if (!$request->has(array_keys($mod))){
            return msg(1,__LINE__);
        }
        //提取数据
        $data = $request->only(array_keys($mod));
        //判断是否存在昵称，没有获取真实姓名并加入
        if ($data["fromName"] === ""||empty($data["fromName"])){
            if ($request->routeIs("EatestComments_update")) {
                $uid = EatestComments::query()->find($request->route('id'))->fromId;
            } else {
                $uid = session("uid");
            }
            $data["nickname"] = User::query()->find($uid)->nickname;
        }
        //判断数据格式
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };
        //查找Eatest发布者id
        $toId = Evaluation::query()->find($request->route('id'))->publisher;
        $data = $data + ["toId"=>$toId];
        return $data;
    }
}
