<?php

namespace App\Http\Controllers\Api\jwxt;


use App\Http\Controllers\Controller;
use App\Model\jwxt\CountDown;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Config\Definition\Exception\Exception;

class CountDownController extends Controller
{

    public function addCountDown(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        //若没有session 判断remember
//        $uid = handleUid($request);
        $uid = 1;
        $data = $data + ["uid" => $uid];
        $countdown = new CountDown($data);

        //发布，同时将评测加入我的倒计时
        if ($countdown->save()) {
            User::query()->find($uid)->add_countdown($countdown->id);
            return msg(0, ["id" => $countdown->id]);
        }
        //未知错误
        return msg(4, __LINE__);

    }

    public function query(Request $request){
        //拉取数据
        $countdown_list=CountDown::query()
            ->where('uid',$request->route('uid'))->orderByDesc("top")
            ->get()->toArray();

        $message = ['total' => count($countdown_list), 'list' => $countdown_list];
        return msg(0,$message);


    }

    // 置顶
    public function top(Request $request)
    {
        //提取数据
//        $uid = handleUid($request);
        $uid = 1;
        //查找目标
        $countdown = CountDown::query()->find($request->route("id"));
        $top = CountDown::query()->find($request->route("id"),['top'])->toArray();
        if ($top['top'] == 1){
            //取消置顶
            $countdown->update(["top" => 0]);
            return msg(0,"置顶取消");
        }else{
            //取消以前的置顶
            $old = CountDown::query()->where("uid","=",$uid)->where("top", "=", "1")->first();
            if ($old) {
                $old->update(["top" => 0]);
            }

            //置顶
            if ($countdown->update(["top" => 1])) {
                return msg(0, __LINE__);
            }
            return msg(4, __LINE__);
        }

    }

    //删除
    public function delete(Request $request){
        //查找
        $countdown=CountDown::query()->find($request->route('id'));
        try{
            //删除User表里倒计时
            User::query()->find($countdown->uid)->del_countdown($countdown->id);
            //删除倒计时
            $countdown->delete();
            return msg(0,"删除成功");
        }catch (Exception $e){
            echo $e;
        }

    }

    public function update(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->data_handle($request);
        //如果$data非函数说明有错误，直接返回
        if (!is_array($data)) {
            return $data;
        }
        //修改
        $countdown = CountDown::query()->find($request->route('id'));
        $countdown = $countdown->update($data);
        if ($countdown) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //检查函数
    private function data_handle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "location" => ["string", "max:20"],
            "target" => ["string", "max:50"],
            "remarks" => ["string", "max:50"],
            "end_time" => ["string", "max:50"]
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
