<?php

namespace App\Http\Controllers\Api\Eatest;

use Illuminate\Support\Facades\Storage;
use \Redis;
use App\Http\Controllers\Controller;
use App\Model\Eatest\Evaluation;
use Illuminate\Http\Request;
use App\User;
use App\Lib\WeChat;
use Illuminate\Support\Facades\Validator;

class EvaluationController extends Controller
{
    //发布
    public function publish(Request $request){
        //通过路由获取前端数据，并判断数据格式
        $data = $this->data_handle($request);
        if (!is_array($data)) {
            return $data;
        }
        //加上额外必要数据
        $data = $data + ["top" => 0, "collections" => 0, "like" => 0, "views" => 0, "publisher" => session("uid")];
        $evaluation = new Evaluation($data);
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
        //发布，同时将评测加入我的发布
        if ($evaluation->save()) {
            User::query()->find(session("uid"))->add_eatest($evaluation->id);

            return msg(0, ["id" => $evaluation->id]);
        }
        //未知错误
        return msg(4, __LINE__);
    }

    //删除
    public function delete(Request $request)
    {
        $files = [];
        $evaluation = Evaluation::query()->find($request->route('id'));
        // 将该评测从我的发布中删除
        User::query()->find($evaluation->publisher)->del_eatest($evaluation->id);

        $imgs = Evaluation::query()->find($request->route('id'))->img;
        $imgs = json_decode($imgs);
        foreach ($imgs as $file){           //遍历结果去掉前缀
            $replace = str_replace(config("app.url")."/storage/image/","",$file);
            $files[] = $replace;
        }
        $disk = Storage::disk('img');
        foreach ($files as $file){   //遍历删除
            $disk->delete($file);
        }
        $evaluation->delete();

        return msg(0, __LINE__);
    }

    //修改
    public function update(Request $request)
    {
        //通过路由获取前端数据，并判断数据格式
        $data = $this->data_handle($request);
        //如果$data非函数说明有错误，直接返回
        if (!is_array($data)) {
            return $data;
        }
        //修改
        $evaluation = Evaluation::query()->find($request->route('id'));
        $evaluation = $evaluation->update($data);
        if ($evaluation) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //拉取
        //拉取单篇信息
    public function get(Request $request)
    {
        $evaluation = Evaluation::query()->find($request->route('id'));
        //判断近期是否浏览过该文章，若没有浏览量+1 and 建立近期已浏览session
        if (
            !session()->has("mark" . $request->route('id'))
            || session("mark" . $request->route('id')) + 1800 < time()
        ) {
            $evaluation->increment("views");
            session(["mark" => time()]);
        }
        $evaluation_list = $evaluation->info();
        $uid = $evaluation_list['publisher'];

        $avatar = User::query()->find($uid)->avatar;
        $evaluation_list = $evaluation_list + ['avatar' => $avatar];

        return msg(0, $evaluation_list);
    }
    //拉取我的列表
    public function get_me_list(Request $request){
        $uid = $request->route('uid');
        $eatest = User::query()->find($uid)->eatest;
        $eatest = array_keys(json_decode($eatest,true));
        #print_r($eatest);
	$evaluation_list = Evaluation::query()->whereIn('id',$eatest)->get([
            "id", "nickname as publisher_name", "label", "views","like",
            "collections", "top", "img", "title", "created_at as time"
        ])->toArray();

        return msg(0,$evaluation_list);
    }
    //拉取我的喜欢
    public function get_like_list(Request $request){
        $uid = $request->route('uid');
        $like = User::query()->find($uid)->like;
        $like = array_keys(json_decode($like,true));
        $evaluation_list = Evaluation::query()->whereIn('id',$like)->get([
            "id", "nickname as publisher_name", "label", "views","like",
            "collections", "top", "img", "title", "created_at as time"
        ])->toArray();

        return msg(0,$evaluation_list);
    }
    //拉取我的收藏
    public function get_collection_list(Request $request){
        $uid = $request->route('uid');
        $collection = User::query()->find($uid)->collection;
        $collection = array_keys(json_decode($collection,true));
        $evaluation_list = Evaluation::query()->whereIn('id',$collection)->get([
            "id", "nickname as publisher_name", "label", "views","like",
            "collections", "top", "img", "title", "created_at as time"
        ])->toArray();

        return msg(0,$evaluation_list);
    }
        //拉取列表信息
    public function get_list(Request $request)
    {
        //判断若拉取首页，则获取推荐美文
        if ($request->route("page") == 1) {
            //获取推荐美文，创建collect_count
            $new_list = $this->get_orderBy_score_list();
        }

        //分页，每页10条
        $offset = $request->route("page") * 10 - 10;
        //获取session
        $value = session('collect_count');
        //若与前面的推荐美文重复，将其剔除 whereNotIn()
        $evaluation_list = Evaluation::query()->limit(10)
            ->offset($offset)->orderByDesc("evaluations.created_at")
            ->whereNotIn('evaluations.id',$value)
            ->leftJoin('users','evaluations.publisher','=','users.id')
            ->get([
                "evaluations.id", "evaluations.nickname as publisher_name", "label", "views","evaluations.like",
                "collections", "top", "img", "title", "users.avatar","evaluations.created_at as time"
            ])->toArray();

        //判断若拉取首页，将推荐美文和正常拉取合并
        if ($request->route("page") == 1) {
            $evaluation_list = array_merge($new_list, $evaluation_list);
            for ($i = 0;$i<3;$i++){
                $new_list_count[] = $new_list[$i]['id'];
            }
        }

        $message = ['total' => count($evaluation_list), 'list' => $evaluation_list];
        return msg(0, $message);
    }

    // 置顶
    public function top(Request $request)
    {
        //取消以前的置顶
        $old = Evaluation::query()->where("top", "=", "1")->first();
        if ($old) {
            $old->update(["top" => 0]);
        }


        $evaluation = Evaluation::query()->find($request->route("id"));
        if (!$evaluation) {
            return msg(3, "目标不存在" . __LINE__);
        }
        //置顶
        if ($evaluation->update(["top" => 1])) {
            return msg(0, __LINE__);
        }

        return msg(4, __LINE__);
    }

    //辅助函数
    private function get_orderBy_score_list()
    {
        //获取前20推荐分值最高美文
        $list = Evaluation::query()->limit(20)->orderByDesc("score")
            ->where("top", "=", "0")
            ->leftJoin('users','evaluations.publisher','=','users.id')
            ->get([
                "evaluations.id", "evaluations.nickname as publisher_name", "label", "views","evaluations.like",
                "collections", "top", "img", "title", "users.avatar","evaluations.created_at as time"
            ])
            ->toArray();

        $new_list = [];
        $new_list_count = [];
        $begin = rand(0, 20);
        //随机抽取三个go
        for ($i = 0; $i < 3; $i += 1) {
            $new_list[] = $list[($begin + $i * 6) % count($list)];
            $new_list_count[] = $list[($begin + $i * 6) % count($list)]["id"];

        }
        //创建需要剔除美文id的session数组
        session(['collect_count' => $new_list_count]);
        return $new_list;
    }


    //检查函数
    private function data_handle(Request $request = null){
        //声明理想数据格式
            $mod = [
                "img" => ["json"],
                "title" => ["string", "max:50"],
                "content" => ["string", "max:400"],
                "label" => ["json"],
                "nickname" => ["string", "max:10"]
            ];
        //是否缺失参数
        if (!$request->has(array_keys($mod))){
            return msg(1,__LINE__);
        }
        //提取数据
        $data = $request->only(array_keys($mod));
        //判断是否存在昵称，没有获取真实姓名并加入
        if ($data["nickname"] === ""||empty($data["nickname"])){
            if ($request->routeIs("evaluation_update")) {
                $uid = Evaluation::query()->find($request->route('id'))->publisher;
            } else {
                $uid = session("uid");
            }
            $data["nickname"] = User::query()->find($uid)->nickname;
        }
        //判断数据格式
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };
        return $data;
    }
}
