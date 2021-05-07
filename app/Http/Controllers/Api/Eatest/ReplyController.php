<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
use App\Model\Eatest\EatestComments;
use App\Model\Eatest\EatestReplies;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReplyController extends Controller
{
    //评论回复
    public function publish(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        $comment = EatestComments::query()->find($request->route('id'));
        if(!$comment) {
            return response(msg(3, "eatest评论不存在" . __LINE__));
        }
        $data = $data + ["status" => 0,"comment_id"=>$request->route("id")];
        $reply = new EatestReplies($data);

        if ($reply->save()) {
            return msg(0, __LINE__);
        }
        //未知错误
        return msg(4, __LINE__);
    }

    public function get_list(Request $request){
        $reply_list = EatestReplies::query()
            ->where('comment_id','=',$request->route('id'))
            ->get([
                'id','fromId','fromName','toId','comment_id','fromAvatar','content','created_at as time'
            ])->toArray();
        $message = ['total' => count($reply_list), 'list' => $reply_list];
        return msg(0, $message);
    }


    //获取指定用户评论回复
    public function getOneList(Request $request){
        //提取数据
        $uid = $request->route('uid');
        $page = $request->route('page');
        $limit = 13;
        $offset = $page * $limit - $limit;
        //查看评论
        $reply = EatestReplies::query()
            ->where([
                ['fromId', $uid],
                ['status', 0]
            ]);
        $list = $reply
            ->limit(13)
            ->offset($offset)
            ->orderByDesc('created_at')
            ->get();
        if(!$list){
            return msg(4,__LINE__);
        }
        $message = ['total' => $reply->count(), 'limit' => $limit, 'list' => $list];
        return msg(0,$message);
    }

    //删除
    public function delete(Request $request)
    {
        $reply = EatestReplies::query()->find($request->route('id'));
        // 将该评测从我的发布中删除
        $reply->delete();
        return msg(0, __LINE__);
    }

    //检查函数
    private function data_handle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "toId" => ["integer"],
            "fromId" => ["integer"],
            "fromName" => ["string", "max:20"],
            "fromAvatar" => ["string"],
            "content" => ["string", "max:50"]

        ];
        //是否缺失参数
        if (!$request->has(array_keys($mod))){
            return msg(1,__LINE__);
        }
        //提取数据
        $data = $request->only(array_keys($mod));

        //判断数据格式
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        return $data;
    }
}
