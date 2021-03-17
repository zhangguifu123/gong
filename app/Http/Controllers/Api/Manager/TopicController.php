<?php

namespace App\Http\Controllers\Api\Manager;

//use App\EatestTopics;
use App\Http\Controllers\Controller;
use App\Model\Eatest\EatestTopics;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    //添加话题
    public function addTopic(Request $request)
    {
        //验证数据格式
        $params = [
            'topicName' => ['string'],
            'isTop' => ['boolean']
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }

        //获取数据
        $topicName = $request->input('topicName');
        $isTop = $request->input('isTop');
        //判断是否置顶,确定返回数据
        if($isTop == true){
            $newTop = (EatestTopics::query()->max('isTop')) + 1;
        }else{
            $newTop = 0;
        }
        $data = ['topicName'=>$topicName,'isTop'=>$newTop];
        //插入记录
        $addTopic = EatestTopics::query()->create($data);
        if($addTopic){
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //删除话题
    public function dropTopic(Request $request)
    {
        //检查数据格式

        //提取数据
        $topicId = $request->route('topicId');
        //删除
        $dropTopic = EatestTopics::destroy($topicId);
        if($dropTopic){
            $data = ['删除话题id' => $topicId];
            return msg(0, $data);
        }
        return msg(4, __LINE__);
    }

    //话题置顶
    public function topOrder(Request $request)
    {
        //检查数据格式

        //提取数据
        $topicId = $request->route('topicId');
        //置顶
        $isTop = (EatestTopics::query()->max('isTop')) + 1;
        if ($isTop){
            $sqlData = ['isTop' => $isTop];
            $topOrder = EatestTopics::query()->find($topicId)->update($sqlData);
            if($topOrder){
                $data = ["修改的话题id" => $topicId];
                return msg(0, $data);
            }
        }
        return msg(4, __LINE__);
    }

    //查看话题
    public function showTopic(Request $request)
    {
        //提取数据
        $page = $request->route('page');
        //查看话题
        $offset = $page * 9 - 9;
        $showTopics = EatestTopics::query()
            ->limit(9)
            ->offset($offset)
            ->orderByDesc('created_at')
            ->get();
        if(!$showTopics){
            return msg(4, __LINE__);
        }
        $data = $showTopics->toArray();
//        foreach ($showTopics as $showTopic){
////            return $showTopic->eatestSum;
//            $data[] =  array(
//                //存疑
//                'topicId' => $showTopic->id,
//                'topicName' => $showTopic->topicName,
//                'eatestSum' => $showTopic->eatestSum
//            );
//        }
//        return \GuzzleHttp\json_decode($data);
//        return $data;
        $message = [ 'total' => count($data), 'list' => $data];
        return msg(0, $message);
    }
}
