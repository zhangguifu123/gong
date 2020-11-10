<?php

namespace App\Http\Controllers\Api\Ecard;

use App\Http\Controllers\Controller;
use App\Model\User\Ecard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PharIo\Manifest\Library;

class LibraryController extends Controller
{
    //
    public function binding(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        $ecard = Ecard::query()->where('stu_id','=',$data['stu_id']);
        //目标不存在
        if (!$ecard){
            return msg(11,__LINE__);
        }
        $ecard = $ecard->update($data);
        if ($ecard) {
            return msg(0,__LINE__);
        }
        //未知错误
        return msg(4, __LINE__);
    }

    public function get(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $stu_id = $request->route('stu_id');
        $Library = Library::query()->where('stu_id','=',$stu_id);
        if (!$Library){
            return msg(3,__LINE__);
        }
        $consume = $Library->select('consume')->get()['0'];
        if ($consume) {
            return msg(0,$consume);
        }
        //未知错误
        return msg(4, __LINE__);
    }

    //检查函数
    private function data_handle(Request $request = null){
        //声明理想数据格式
        $mod = [
            "stu_id" => ["integer"],
            "library" => ["string"],
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
