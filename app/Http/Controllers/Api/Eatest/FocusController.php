<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
use App\Model\Eatest\Evaluation;
use App\Model\Eatest\FocusOn;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Null_;

class FocusController extends Controller
{

    public function focus(Request $request)
    {
        //若没有session 判断remember
        $uid = handleUid($request);

        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        //处理数据
        $data = $data + ["uid" => $uid];
        if ($this->checkFocused($data) == true){
            $data += ["mutual" => 1];
        }

        $focus = new FocusOn($data);
        if ($focus->save()){
            //增减粉丝
            User::query()->find($data['follow_uid'])->increment('focused');
            //增减关注数
            $userFocus = User::query()->find($data['uid']);
            $userFocus->increment('focus');

            $result = ['userFocusCount' => $userFocus->focus,'ifMutual' => $focus->mutual];
            return msg(0,$result);
        }
        return msg(4, __LINE__);

    }

    public function unfocus(Request $request){
        //若没有session 判断remember
        $uid = handleUid($request);

        if (!$request->has('unFollowId')) {
            return msg(1, "缺失参数");
        }
        $mod = ['unFollowId' => ["boolean"]];

        $data = $request->only(array_keys($mod));
        $validator = Validator::make($data, $mod);
        if ($validator->fails()) {
            return msg(1, '非法参数' . __LINE__);
        }
        //数据封装
        $data = ['unFollowId'=> $data['unFollowId'] , 'uid' => $uid];
        //删除关注
        $focusOn = FocusOn::query()->where('follow_uid',$data['unFollowId'])->where('uid',$data['uid']);
        if (!$focusOn->delete()) {
            return msg(4, __LINE__);
        }

        //增减粉丝和关注数
        User::query()->find($data['unFollowId'])->decrement('focused');
        $user = User::query()->find($data['uid']);
        $user->decrement('focus');

        //取消互相关注
        $this->checkFocused($data);

        return msg(0,['userFocusCount' => $user->focus]);
    }

    //获取关注or粉丝列表
    public function get_user_focus_list(Request $request)
    {
        //
        $user_id = $request->route("uid");
        $type    = $request->route('type');

        if ('focusing' == $type){
            $typeBefore = 'uid';
            $typeLater = 'follow_id';
        }

        if ('focused' == $type){
            $typeBefore = 'follow_uid';
            $typeLater = 'uid';
        }

        $user = User::query()->find($user_id);
        if (!$user) {
            return msg(3, "目标不存在" . __LINE__);
        }
        $focus_list = FocusOn::query()->where($typeBefore,$user_id)->get($typeLater);
        $focus_list = DB::table("users")->whereIn('id',$focus_list)
            ->get(["id", "nickname", "name", "collection",
                "like", "eatest", "focused", "focus", "avatar"])
            ->toArray();
        $list_count = $user->focus;
        $message    = ['total'=>$list_count,'list'=>$focus_list];
        return msg(0, $message);
    }

    //检查函数
    private function data_handle(Request $request){
        //声明理想数据格式
        $mod = [
            "follow_uid"  => ["integer"],
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

    private function checkFocused(array $data){
        //设置互相关注
        if (isset($data['follow_uid'])){
            $check = FocusOn::query()->where('uid',$data['follow_uid'])->where('follow_uid',$data['uid']);
            if (!$check->get()->isEmpty()){
                $check->update(['mutual' => 1]);
                return true;
            }else{
                return false;
            }
        }
        //取消互相关注
        if (isset($data['unFollowId'])){
            $check = FocusOn::query()->where('uid',$data['unFollowId'])->where('follow_uid',$data['uid']);
            if (!$check->get()->isEmpty()){
                $check->update(['mutual' => 0]);
                return true;
            }else{
                return false;
            }
        }

    }

}
