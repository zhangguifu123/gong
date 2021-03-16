<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Model\Manager\Tip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipController extends Controller
{
    //
    public function upload(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        $tip = new Tip($data);
        if ($tip->save()){
            return msg(0,__LINE__);
        }else{
            //未知错误
            return msg(4, __LINE__);
        }
    }

    public function get_list(Request $request)
    {
        //分页，每页10条
        $offset = $request->route("page") * 10 - 10;
        //若与前面的推荐美文重复，将其剔除 whereNotIn()
        $tip_list = Tip::query()->limit(10)
            ->offset($offset)->orderByDesc("created_at")
            ->get([
                'eatest_id', 'reason', 'content', 'img','status','fromId','toId','reporter','remarks','created_at as time'
            ])->toArray();


        $message = ['total' => count($tip_list), 'list' => $tip_list];
        return msg(0, $message);
    }

    public function update_status(Request $request){
        //检查是否存在数据格式
        $mod = [
            "handle" => ["string"],
            "remarks" => ["string"],
        ];
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }
        //数据格式是否正确
        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };
        $data = $data + ['status' => 1];
        //修改
        $tip = Tip::query()->find($request->route('id'));
        $tip = $tip->update($data);
        if ($tip) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //检查函数
    private function data_handle(Request $request){
        //声明理想数据格式
        $mod = [
            "eatest_id" => ["integer"],
            "reason" => ["string"],
//            "content" => ["string"],
            "img" => ['json'],
            "fromId" => ["integer"],
            "toId" => ["integer"],
            "reporter" => ["string"]
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
