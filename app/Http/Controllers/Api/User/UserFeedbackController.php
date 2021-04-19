<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Model\User\UserFeedback;
use Illuminate\Http\Request;

class UserFeedbackController extends Controller
{
    //
    public function addFeedback(Request $request)
    {
        //检查数据格式
        $params = [
            'stu_id' => ['integer'],
            'content' => ['string'],
            'phone' => ['integer'],
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $data = $request->only(array_keys($params));
        //添加反馈
        $Feedback = UserFeedback::query()->create($data);
        if($Feedback){
            return msg(0,__LINE__);
        }
        return msg(4,__LINE__);
    }
}
