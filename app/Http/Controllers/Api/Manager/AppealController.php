<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Model\Manager\EatestAppeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


/**
 * Class AppealController
 * @package App\Http\Controllers\Api\Manager
 * $handle = [
 *      0 => 等待处理
 *      1 => 无效申诉
 *      2 => 内容还原
 * ]
 * $type = [
 *      0 => eatest内容举报
 *      1 => eatest评论举报
 * ]
 *
 */
class AppealController extends Controller
{
    //添加申诉
    public function addAppeal(Request $request)
    {
        //检查数据格式
        $params = [
            "eatestId" => ["integer"],
            "userName" => ["string"],
            "type" => ["string"],
            "content" => ['string'],
            "describe" => ["string"]
        ];
        $request = handleData($request, $params);
        if (!is_object($request)) {
            return $request;
        }
        //提取数据
        $data = $request->only(array_keys($params));
        //添加申诉
        $addAppeal = EatestAppeal::query()->create($data);
        if ($addAppeal) {
            return msg(0, __LINE__);
        }
        //未知错误
        return msg(4, __LINE__);
    }

    //查看所有申诉
    public function showAppeal(Request $request)
    {
        //提取数据
        $status = $request->route('status');
        if($status == 0){
            $status = [0];
        }else{
            $status = [1,2];
        }
        //分页，每页10条
        $offset = $request->route("page") * 13 - 13;
        $showAppeal = EatestAppeal::query()
            ->whereIn('status',$status)
            ->limit(13)
            ->offset($offset)
            ->orderByDesc("created_at")
            ->get();
        if(!$showAppeal){
            return msg(4,__LINE__);
        }
        $data = $showAppeal->toArray();
        $message = ['total' => count($data), 'list' => $data];
        return msg(0, $message);
    }


    //申诉处理
    public function handleAppeal(Request $request)
    {
        //检查数据格式
        $params = [
            "status" => ["integer"],
        ];
        $request = handleData($request, $params);
        if (!is_object($request)) {
            return $request;
        }
        //提取数据
        $status = $request->input('status');
        $appealId = $request->route('id');
        //修改
        $sqlData = ['status' => $status];
        $appeal = EatestAppeal::query()->find($appealId)->update($sqlData);
        if ($appeal) {
            $data = ['修改的申诉id' => $appealId];
            return msg(0, $data);
        }
        return msg(4, __LINE__);
    }

}

