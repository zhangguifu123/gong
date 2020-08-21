<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
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
            return msg(0, __LINE__);
        }
        //未知错误
        return msg(4, __LINE__);
    }

    //删除
    public function delete(Request $request)
    {
        $Comments = EatestComments::query()->find($request->route('id'));
        // 将该评测从我的发布中删除
        $Comments->delete();

        return msg(0, __LINE__);
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
