<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Lib\Sensitive;
use App\Lib\Suspicious;
use App\Model\Eatest\CommentReview;
use App\Model\Eatest\EatestComments;
use App\Model\Eatest\EatestReplies;
use App\Model\Eatest\EatestReview;
use App\Model\Eatest\Evaluation;
use App\Model\Eatest\ReplyReview;
use Illuminate\Http\Request;

//require "DFAFiltter.php";
//use App\Lib\;


/**
 * $status = [
 *      0 => '待审核'
 *      1 => '已上架'
 *      2 => '已下架'
 * ]
 */
class ReviewController extends Controller
{

    //敏感词过滤
    public function sensitiveFilter(Request $request)
    {
        //检查数据格式
//        $params = [
//            'id' => ['integer'],      //评测id
//            'content' => ['string']
//        ];
//        $request = handleData($request,$params);
//        if(!is_object($request)){
//            return $request;
//        }
        //提取数据
        $content = $request->input('content');

        //敏感词过滤
        $replace = new Sensitive();
        $replaced = $replace->execFilter($content);
        if($replaced !== $content){
            return msg(4,__LINE__);
        }
        return msg(0,__LINE__);
//        return $this->suspiciousFilter($request);

    }

    //eatest可疑内容提取
    public function eatestFilter(Request $request)
    {
        //检查数据格式
//        $params = [
//            'content' => ['string']
//        ];
//        $request = handleData($request,$params);
//        if(!is_object($request)){
//            return $request;
//        }
        //提取数据
        $content = $request->input('content');
        $id = $request->input('id');
//        return $content;

        //可疑内容提取
        $replace = new Suspicious();
        $replaced = $replace->execFilter($content);
//        var_dump(explode($replaced));
//        var_dump(array_diff(str_split($content), str_split($replaced)));
        return $replaced;
        if($replaced !== $content){
            //可疑词分块数组提取

            $suspiciousWord = [];
//            $create = EatestReview::query()
            return msg(0,__LINE__);
        }
        Evaluation::query()->where('id',$id)->update(['status' => 1]);
        return msg(0,__LINE__);
    }


    //eatest评论可疑内容提取
    private function commentFilter($request)
    {
        //检查数据格式
//        $params = [
//            'content' => ['string']
//        ];
//        $request = handleData($request,$params);
//        if(!is_object($request)){
//            return $request;
//        }
        //提取数据
        $content = $request->input('content');
        $id = $request->input('id');
//        return $content;

        //可疑内容提取
        $replace = new Suspicious();
        $replaced = $replace->execFilter($content);
        if($replaced !== $content){
            //可疑词分块数组提取
            $suspiciousWord = [];

            return msg(0,__LINE__);
        }
        Evaluation::query()->where('id',$id)->update(['status' => 1]);
        return msg(0,__LINE__);
    }


    //eatest评论回复可疑内容提取
    private function replyFilter($request)
    {
        //检查数据格式
//        $params = [
//            'content' => ['string']
//        ];
//        $request = handleData($request,$params);
//        if(!is_object($request)){
//            return $request;
//        }
        //提取数据
        $content = $request->input('content');
        $id = $request->input('id');
//        return $content;

        //可疑内容提取
        $replace = new Suspicious();
        $replaced = $replace->execFilter($content);
        if($replaced !== $content){
            //可疑词分块数组提取
            $suspiciousWord = [];

            return msg(0,__LINE__);
        }
        Evaluation::query()->where('id',$id)->update(['status' => 1]);
        return msg(0,__LINE__);
    }

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
        $list = EatestReview::query()
//            ->where('status',0)
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
        $list = CommentReview::query()
//            ->where('status',0)
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

    //查看待审核评论回复
    public function getReplyList(Request $request){
        //提取数据
        $page = $request->route('page');
        //查看评论
        $offset = $page * 13 -13;
        $list = ReplyReview::query()
//            ->where('status',0)
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


    //评论回复上架/下架
    public function updateReplyStatus(Request $request)
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
        $evaluation = EatestReplies::query()->find($request->route('id'));
        $evaluation = $evaluation->update($data);
        if ($evaluation) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }
}



