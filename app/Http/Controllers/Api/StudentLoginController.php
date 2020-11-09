<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\User\Ecard;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentLoginController extends Controller
{
    public function login(Request $request)
    {
        session(['login' => false, 'uid' => null]);
        // 带remember的请求直接通过
        if ($request->has(["remember"])) {
            $user = User::query()->where('remember', $request->input('remember'))->first();
            if ($user) {
                return msg(0, $user->info());
            }
        }

        $mod = array(
            'stu_id' => ['regex:/^20[\d]{8,10}$/'],
            'password' => ['regex:/^[^\s]{8,20}$/'],
        );
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }
        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };
        $user = User::query()->where('stu_id', $data['stu_id'])->first();

        if (!$user) { // 该用户未在数据库中 用户名错误 或 用户从未登录
            //利用三翼api确定用户账号密码是否正确
            $output = checkUser($data['stu_id'], $data['password']);
            if ($output['code'] == 0) {
                $user = new User([
                    'nickname'   => "快来想个昵称吧",
                    'avatar'     => json_encode( config("app.url")."/storage/avatar/avatar.jpg"),
                    'name'       => $output['data']['name'], //默认信息
                    'stu_id'     => $data['stu_id'],
                    'password'   => md5($data['password']),
                    'like'       => '[]',
                    'eatest'     => '[]', //mysql 中 json 默认值只能设置为NULL 为了避免不必要的麻烦，在创建的时候赋予初始值
                    'upick'      => '[]',
                    'countdown'  => '[]',
                    'collection' => '[]',
                    'remember'   => md5($data['password'] . time() . rand(1000, 2000))
                ]);
                //消费系统
                $ecard  = new Ecard([
                    'stu_id'     => $data['stu_id'],
                    'name'       => $output['data']['name'], //默认信息
                    'consume'      => '0',
                    'library'    => '0'
                ]);
                $resultUser = $user->save();
                $resultEcard = $ecard->save();

                if ($resultUser && $resultEcard) {
                    //直接使用上面的 $user 会导致没有id  这个对象新建的时候没有id save后才有的id 但是该id只是在数据库中 需要再次查找模型
//                    $user = User::query()->where('stu_id', $data['stu_id'])->first();
                    session(['login' => true, 'uid' => $user->id]);

                    return msg(0, $user->info());
                } else {
                    return msg(4, __LINE__);
                }
            }
        } else { //查询到该用户记录
            if ($user->password === md5($data['password'])) { //匹配数据库中的密码
                session(['login' => true, 'uid' => $user->id]);
                return msg(0, $user->info());
            } else { //匹配失败 用户更改密码或者 用户名、密码错误
                $output = checkUser($data['stu_id'], $data['password']);
                if ($output['code'] == 0) {
                    $user->password = md5($data['password']);
                    $user->remember = md5($data['password'] . time() . rand(1000, 2000));
                    $user->save();

                    session(['login' => true, 'uid' => $user->id]);

                    return msg(0, $user->info());
                }
            }
        }
        return msg(2, __LINE__);
    }

    public function update_nickname(Request $request){
        $user = User::query()->find(session('uid'));
        $name = $request->input('nickname');
        if (!$name){
            return msg(1,__LINE__);
        }
        if (!is_string($name)){
            return msg(3,__LINE__);
        }
        $user->update(['nickname' => $name]);
        return msg(0,__LINE__);
    }

    public function get_user_publish_list(Request $request)
    {
        $user_id = $request->route("uid");
        $user = User::query()->find($user_id);
        if (!$user) {
            return msg(3, "目标不存在" . __LINE__);
        }
        $publish_id_list = array_keys(json_decode($user->publish, true));

        $publish_list = DB::table("evaluations")->whereIn("evaluations.id", $publish_id_list)
            ->get(["id", "nickname as publisher_name", "tag","like","avatar", "views",
                "collections","img", "title", "location", "shop_name", "created_at as time"])->toArray();
        $list_count =  DB::table("evaluations")->whereIn("evaluations.id", $publish_id_list)
            ->count();
        $message = ['total'=>$list_count,'list'=>$publish_list];
        return msg(0, $message);
    }
}
