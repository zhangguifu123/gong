<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
use App\Model\Eatest\EatestReplies;
use App\Model\Eatest\Evaluation;
use App\User;
use App\Model\Eatest\EatestComments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    //获取指定用户评论
    public function getOneList(Request $request){
        //提取数据
        $uid = $request->route('uid');
        $page = $request->route('page');
        $limit = 13;
        $offset = $page * $limit - $limit;
        //查看评论
        $comment = EatestComments::query()
            ->where([
                ['eatest_comments.fromId', $uid],
            ])
            ->leftJoin('users', 'eatest_comments.fromId', '=', 'users.id')
            ->whereIn('eatest_comments.handleStatus', [0, 1]);
        $listSum = $comment->count();
        $list = $comment
            ->limit(13)
            ->offset($offset)
            ->orderByDesc('eatest_comments.created_at')
            ->get(['eatest_comments.id', 'eatest_comments.eatest_id', 'eatest_comments.toId', 'eatest_comments.fromId', 'eatest_comments.status', 'users.nickname as fromName', 'users.avatar as fromAvatar', 'eatest_comments.content', 'eatest_comments.like', 'eatest_comments.handleStatus', 'eatest_comments.created_at', 'eatest_comments.updated_at']);
        if(!$list){
            return msg(4,__LINE__);
        }
        $message = ['total' => $listSum, 'limit' => $limit, 'list' => $list];
        return msg(0,$message);
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
                //若没有session 判断remember
                $uid = handleUid($request);
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


    public function like(Request $request)
    {
        //检查是否存在数据格式
        $mod = ["like" => ["boolean"]];
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }
        //数据格式是否正确
        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        //若用remember登陆，获取 uid
        $mod = [
            "remember"      => ["string"],
            "uid"    => ["string", "max:50"]
        ];
        //是否默认登陆
        if ($request->has(array_keys($mod))){
            $uid = $request->input('uid');
        }else{
            $uid = session('uid');
        }

        $data = ["user" => $uid, "comment" => $request->route("id"), "like" => $request->input("like")];
        // 事务处理
        DB::beginTransaction();
        try {
            //获取likes表数据，条件查询 user、eva_id
            $like = DB::table("comment_likes")->where("user", $uid)->where("comment", $request->route("id"));
            //获取Eatest or Upick表数据，条件查询 eva_id
            $comment = DB::table('eatest_comments')->where('id', $data["comment"]);

            // 赞/踩
                if($data["like"] != 1) {
                    if($like->count()) { //曾经赞过则为取消赞
                        $comment->increment('like', -1);
                        $like->delete();
                    }
                } else {
                    if($like->count()) { //曾经赞过则为取消赞
                        return msg(7,'请勿重复点赞');
                    }
                    $comment->increment('like', 1);
                    DB::table("comment_likes")->insert($data);
                }
            DB::commit();

            return msg(0, __LINE__);
        } catch (\Exception $e) {
            DB::rollBack();
            print_r($e);
            return msg(7, __LINE__);
        }
    }
}
