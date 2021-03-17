<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Model\Eatest\Evaluation;
use App\User;
use Illuminate\Http\Request;


/**
 * Class UserController
 * @package App\Http\Controllers\Api\Manager
 * $status = [
 *      0 => '正常',
 *      1 => '禁言一天',
 *      2 => '禁言三天',
 *      3 => '永久禁言'
 * ]
 */
class UserController extends Controller
{
    //查看全部用户
    public function showUser(Request $request)
    {
        //提取数据
        $page = $request->route('page');
        $offset = $page * 13 - 13;
        $showUser = User::query()
            ->limit(13)
            ->offset($offset)
            ->orderByDesc('created_at');
//            ->get(['id','nickname','stu_id','status']);
        if(!$showUser){
            return msg(4,__LINE__);
        }
        $datas = $showUser->get(['id','nickname','stu_id','status'])->toArray();
        foreach ($datas as $dat){
            $eatestTotal = Evaluation::query()->where('publisher',$dat['id'])->count();
            $dat['eatestTotal'] = $eatestTotal;
            $data[] = $dat;
        }
        $message = ['total' => count($data),'list' => $data];
        return msg(0,$message);
    }

    //手动禁言/解除
    public function updateStatus(Request $request)
    {
        //检查数据格式
        $params = [
            'status' => ['integer']
        ];
//        return $request;
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $id = $request->route('id');
        $data = $request->only(array_keys($params));
        $updateStatus = User::query()->find($id)->update($data);
        if($updateStatus){
            return msg(0,__LINE__);
        }
        return msg(4,__LINE__);
    }

}
