<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Manager;
use Illuminate\Support\Facades\Validator;


class ManagerController extends Controller
{
    //登录
    public function login(Request $request)
    {
        session(['ManagerLogin' => false, 'mid' => null, 'level' => null]);
        $mod = array(
            'stu_id' => ['regex:/^[\w]{5,12}$/'],
            'password' => ['regex:/^[^\s]{8,20}$/'],
        );
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }
        $data = $request->only(array_keys($mod));

        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };


        $manager = Manager::query()->where('stu_id', '=', $data['stu_id'])->first();
        if (!$manager) {
            return msg(2, "管理员帐号密码错误" . __LINE__);
        } else {
            if ($manager['password'] == 'never_login') { //用户从未登录
                //利用三翼api确定用户账号密码是否正确
                $output = checkUser($data['stu_id'], $data['password']);
                if ($output['code'] == 0) {
                    $data = [
                        'name' => $output['data']['name'],
                        'password' => md5($data['password']),
                        'stu_id' => $data['stu_id'],
                        'level' => '1'
                    ];
                    $result = $manager->update($data);

                    if ($result) {
                        session(['ManagerLogin' => true, 'mid' => $manager->id, 'level' => $manager->level]);
                        return msg(0, $manager->info());
                    } else {
                        return msg(4, __LINE__);
                    }
                } else {
                    return msg(2, __LINE__);
                }
            } else { // 曾经登录过
                if ($manager->password === md5($data['password'])) { //匹配数据库中的密码
                    session(['ManagerLogin' => true, 'mid' => $manager->id, 'level' => $manager->level]);
                    return msg(0, $manager->info());
                } else { //匹配失败 用户更改密码或者 用户名、密码错误
                    //利用三翼api确定用户账号密码是否正确
                    $output = checkUser($data['stu_id'], $data['password']);
                    if ($output['code'] == 0) {
                        $data = [
                            'password' => md5($data['password']),
                        ];
                        $result = $manager->update($data);
                        if ($result) {
                            session(['ManagerLogin' => true, 'mid' => $manager->id, 'level' => $manager->level]);
                            return msg(0, $manager->info());
                        } else {
                            return msg(4, __LINE__);
                        }
                    } else {
                        return msg(2, "帐号密码错误" . __LINE__);
                    }

                }
            }

        }
    }

    //添加管理员
    public function add(Request $request)
    {
        $mod = [
            "stu_id" => ["string", "regex:/^20[\d]{8,10}$/"]
        ];
        if (!$request->has(array_keys($mod))) {
            return msg(1, __LINE__);
        }

        $data = $request->only(array_keys($mod));
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误' . __LINE__);
        };

        // 防止重复添加管理员
        $manager = Manager::query()->where("stu_id", $data["stu_id"])->first();
        if($manager) {
            return msg(8, "管理员已存在" . __LINE__);
        }

        $data = $data + [
                "name" => "从未登录",
                "level" => 1,
                "password" => 'never_login'
            ];

        $manager = new Manager($data);
        $request = $manager->save();
        if ($request) {
            return msg(0, __LINE__);
        } else {
            return msg(4, __LINE__);
        }
    }


    //删除管理员

    public function delete(Request $request)
    {
        $manager = Manager::query()->find($request->route("id"));
        if (!$manager) {
            return msg(3, "目标不存在" . __LINE__);
        } else if($manager->level <= session("level")) {
            return msg(3, "权限不足" .__LINE__);
        }
        $result = $manager->delete();
        if ($result) {
            return msg(0, __LINE__);
        } else {
            return msg(4, __LINE__);
        }
    }


    //获取管理员列表

    public function get_list()
    {
        $manager_list = Manager::query()->get(['id', 'name', 'stu_id', 'level'])->toArray();
        $level = [
            "0" => "超级管理员",
            "1" => "普通管理员"
        ];

        foreach ($manager_list as &$manager) {
            $manager["level"] = $level[$manager["level"]];
        }
        $list_count = Manager::query()->count();
        $message = ['total'=>$list_count,'list'=>$manager_list];
        return msg(0, $message);
    }
}
