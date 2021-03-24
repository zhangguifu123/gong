<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Model\Eatest\EatestComments;
use App\Model\Eatest\EatestReplies;
use App\Model\Eatest\Evaluation;
use Illuminate\Http\Request;



/**
 * $status = [
 *      0 => '待审核'
 *      1 => '已上架'
 *      2 => '已下架'
 * ]
 */
class ReviewController extends Controller
{
    //查看待审核评测
    public function getEvaluationList(Request $request)
    {
        //检查数据格式
//        $params = [
//            'status' => [integer]
//        ];
//        $request = handleData($request,$params);
//        if(!is_object($request)){
//            return $request;
//        }
        //提取数据
//        $data = $request->input('status');
        $page = $request->route('page');
        //查看评测
        $offset = $page * 13 - 13;
        $list = Evaluation::query()
            ->where('status',0)
            ->limit(13)
            ->offset($offset)
            ->orderByDesc('created_at')
            ->get();
        if(!$list){
            return msg(4,__LINE__);
        }
        $message = ['total' => count($list),'list' => $list];
        return msg(0,$message);
    }


    //查看待审核评论
    public function getCommentList(Request $request){
        //提取数据
        $page = $request->route('page');
        //查看评论
        $offset = $page * 13 -13;
        $list = EatestComments::query()
            ->where('status',0)
            ->limit(13)
            ->offset($offset)
            ->orderByDesc('created_at')
            ->get();
        if(!$list){
            return msg(4,__LINE__);
        }
        $message = ['total' => count($list),'list' => $list];
        return msg(0,$message);
    }


    //评测上架/下架
    public function updateEvaluationStatus(Request $request)
    {
        //检查是否存在数据格式
        $mod = ["status" => ["integer"]];
        $request = handleData($request,$mod);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $data = $request->only(array_keys($mod));
        //修改
        $evaluation = Evaluation::query()->find($request->route('id'));
        $evaluation = $evaluation->update($data);
        if ($evaluation) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }


    //评论上架/下架
    public function updateCommentStatus(Request $request)
    {
        //检查是否存在数据格式
        $mod = ["handleStatus" => ["integer"]];
        $request = handleData($request,$mod);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $data = $request->only(array_keys($mod));
        //修改
        $evaluation = EatestComments::query()->find($request->route('id'));
        $evaluation = $evaluation->update($data);
        if ($evaluation) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }
}
