<?php

namespace App\Http\Controllers\Api\Manager;


use App\Model\Eatest\EatestComments;
use App\Model\Eatest\Evaluation;
use App\Model\Manager\EatestReports;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;


/**
 * Class ReportController
 * @package App\Http\Controllers\Api\Manager
 *
 * $handle = [
 *      0 => 等待处理
 *      1 => 无效举报
 *      2 => 内容下架
 * ]
 */
class ReportController extends Controller
{

    //举报评测
    public function addReport(Request $request){
            //检查数据类型
            $params = [
                'eatestId' => ['integer'],
                'userName' => ['string'],
                'targetName' => ['string'],
                'type' => ['string'],
                'describe' => ['string'],
                'reason' => ['string'],
//                'prove' => ['json']
            ];
            $request = handleData($request,$params);
            if(!is_object($request)){
                return $request;
            }
            //获取数据
            $data = $request->only(array_keys($params));
            $addReport = EatestReports::query()->create($data);
            if($addReport){
                return msg(0,__LINE__);
            }
            return msg(4,__LINE__);
    }


    //查看举报(已处理/未处理/分页处理)
    public function showReport(Request $request){
//        //测试
//        $data = User::query()->whereIn('id',[20,21])->get()->toArray();
//        return $data;


//        //检查数据格式
//        $params = [
//            'reportResult' => ['integer']
//        ];
//        $request = handleData($request,$params);
//        if(!is_object($request)){
//            return $request;
//        }
        //提取数据
//        $reportResult = $request->input('reportResult');
        $reportResult = $request->route('status');
        if($reportResult == 0){
            $reportResult = [0];
        }else if($reportResult == 1){
            $reportResult = [1,2];
        }
        //查看举报

        $offset = $request->route('page') * 5 - 5;
        $showReports = EatestReports::query()
            ->whereIn('reportResult',$reportResult)
            ->limit(5)
            ->offset($offset)
            ->orderByDesc('created_at')
            ->get();
        if(!$showReports){
            return msg(4,__LINE__);
        }
        $data = $showReports->toArray();
        $message = ['total' => count($data), 'list' => $data];
        return msg(0,$message);
    }


    //处理举报
    public function handleReport(Request $request){
//        return $request;
//        检查数据格式
        $params = [
            'reportResult' => ['integer']
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $reportId = $request->route('reportId');
        $sqlData = $request->only(array_keys($params));
//        return $sqlData;
        //处理举报
        $handleData = EatestReports::query()->find($reportId)->update($sqlData);
        if($handleData){
            $data = ['处理的举报id' => $reportId];
            return msg(0,$data);
        }
        return msg(4,__LINE__);
    }
}
