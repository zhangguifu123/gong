<?php

namespace App\Http\Controllers\Api\Manager;


use App\Model\Eatest\EatestComments;
use App\Model\Eatest\Evaluation;
use App\Model\Manager\EatestReports;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
//    const ReportType = array(
//        '0' => 'eatest内容举报',
//        '1' => 'eatest评论举报'
//    );
//    const ReportReason = array(
//        '0' => '发布不良信息',
//        '1' => '虚假信息'
//    );
//    const ReportResult = array(
//        '0' => '内容删除',
//        '1' => '已下架',
//        '2' => '无效举报'
//    );


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
                'prove' => ['json']
            ];
            $request = handleData($request,$params);
            if(!is_object($request)){
                return $request;
            }
            //获取数据
            $data = $request->all()->toArray();
//            $eatestId = $request->input('eatestId');
//            $reportType = $request->input('reportType');
//            $reportDescribe = $request->input('reportDescribe');
//            $reportReason = $request->input('reportReason');
//            $reportProve = $request->input('reportProve');
//            //参数二次检查
//            if($reportType != 'eatest内容举报' && $reportType != 'eatest评论举报'){
//                return msg(3,'非法参数' . __LINE__);
//            }
//            if($reportReason != '发布不良信息' && $reportReason != '虚假信息'){
//                return msg(3,'非法参数' . __LINE__);
//            }
//            //获取用户名
//            $userName = User::find($request->input('userId'))->nickname;
//            $targetName = User::find($request->input('targetId'))->nickname;

            //添加举报

//            $data = [
//                'eatestId' => $eatestId,
//                'userName' => $userName,
//                'targetName' => $targetName,
//                'reportType' => $reportType,
//                'reportDescribe' => $reportDescribe,
//                'reportReason' => $reportReason,
//                'reportProve' => $reportProve
//            ];
            $addReport = EatestReports::query()->create($data);
            if($addReport){
                return msg(0,__LINE__);
            }
            return msg(4,__LINE__);
    }


    //查看举报(已处理/未处理/分页处理)
    public function showReport(Request $request){
        //查看举报
        $offset = $request->route('page') * 5 - 5;
        $showReports = EatestReports::query()
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
        //检查数据格式
        $params = [
            'reportResult' => ['string']
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $reportId = $request->route('reportId');
        var_dump($reportId);
        return "0";
        $sqlData = $request->all();
        //处理举报
        $handleData = EatestReports::query()->find($reportId)->update($sqlData);
        if($handleData){
            $data = ['处理的举报id' => $reportId];
            return msg(0,$data);
        }
        return msg(4,__LINE__);
    }
}
