<?php

namespace App\Http\Controllers\Api\Eatest;

use \Redis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Eatest\Food;

class FoodController extends Controller
{
    //发布美食信息
    public function publish(Request $request) {
        $data = $this->data_handle($request);
        if(!is_array($data)) {
            return $data;
        }
        //获取图片链接
        $imgs = json_decode($data['img']);
        //连接Redis
        try {
            $redis = new Redis();
            $redis->connect('gong_redis', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        //删除路由记录
        foreach ($imgs as $i) {
            $redis->hDel('images', $i);
        }
        $data = $data + ["publisher" => session("mid")];
        $food = new Food($data);
        if($food->save()) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    //更新美食信息
    public function update(Request $request) {
        $data = $this->data_handle($request);
        if(!is_array($data)) {
            return $data;
        }
        $food = Food::query()->find($request->route('id'));

        $food = $food->update($data);
        if($food) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    //删除美食信息

    public function delete(Request $request) {
        $food = Food::query()->find($request->route('id'));

        // 将该评测从我的发布中删除
        $food->delete();

        return msg(0, __LINE__);
    }


    //获取美食信息列表

    public function get_list(Request $request) {
        $food_list = Food::query()->orderByDesc("food.id")
            ->leftJoin("managers", "food.publisher", "=", "managers.id")
            ->get([
                "food.id as id", "managers.name as publisher", "nickname", "location",
                "img",  "discount", "food.created_at as time","collections"
            ])->toArray();

        $list_count = Food::query()->count();
        $message = ['total'=>$list_count,'list'=>$food_list];
        return msg(0, $message);

    }

    //用户获取推荐美食信息
    public function get()
    {
        try {
            $redis = new Redis();
            $redis->connect('gong_db', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }

        $info = $redis->hGetAll(session("uid")); // 获取该用户相关信息
        if (empty($info) || $info["date"] != date("Y-m-d")) { // 新用户，或者新的一天，刷新
            $info["date"] = date("Y-m-d");
            $info["times"] = 0;
            $info["id"] = Food::query()->inRandomOrder()->first()->id;
            $redis->hMSet(session("uid"), $info);
        }

        return msg(0, Food::query()->find($info["id"]));
    }



    private function data_handle(Request $request=null) {
        $mod = [
            "img"       => ["json"],
            "location"  => ["string", "max:50"],
            "nickname" => ["string", "max:40"],
            "discount" => ["string","max:50"]
        ];

        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }

        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        return $data;
    }
}
