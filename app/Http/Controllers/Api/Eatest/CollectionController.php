<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    // 收藏/取消收藏
    public function keep(Request $request)
    {
        //检查参数
        if (!$request->has('action')) {
            return msg(1, "缺失参数");
        }
        //检查参数格式
        $mod = ['action' => ["regex:/^keep$|^unkeep$/"]];
        $data = $request->only(array_keys($mod));
        $validator = Validator::make($data, $mod);
        if ($validator->fails()) {
            return msg(1, '非法参数' . __LINE__);
        }

        $user = User::query()->find(session("uid"));
        $evaluation_id = $request->route("id");
        $evaluation = Evaluation::query()->find($evaluation_id);

        if ($request->input("action") == "keep") {
            if ($user->add_collection($evaluation_id)) {
                $evaluation->increment("collections");
                $evaluation->increment("score");
            } else {
                return msg(3, __LINE__);
            }
        } else {
            if ($user->del_collection($evaluation_id)) {
                $evaluation->decrement("collections");
                $evaluation->decrement("score");
            } else {
                return msg(3, __LINE__);
            }
        }

        return msg(0, __LINE__);
    }
}
