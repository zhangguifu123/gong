<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
use App\Model\Eatest\Evaluation;
use App\User;
use Illuminate\Http\Request;

class searchEatestController extends Controller
{
    //模糊搜索
    public function search(Request $request)
    {
        //检查数据格式
//        $params = [
//            'index' => ['string']
//        ];
////        $request = handleData($request,$params);
//        if(!is_object($request)){
//            return $request;
//        }
        //获取数据
        $index = $request->route('index');
        $page = $request->route('page');
//        return $page;
        $offset = $page * 8 - 8;
//        var_dump($index);
        //拉取所有符合条件的评测
        $evaluation_list = Evaluation::query()
            ->limit(8)
            ->offset($offset)
            ->orderByDesc("evaluations.created_at")
            ->where('title','like','%' . $index . '%')      //标题
            ->orwhere('content','like','%' . $index . '%')      //内容
            ->orWhere('label','like','%' . $index . '%')      //标签
            ->orWhere('topic', 'like','%' . $index . '%')       //话题
            ->whereIn('evaluations.status',[0,1])
            ->leftJoin('users','evaluations.publisher','=','users.id')
            ->get([
                "evaluations.id", "users.nickname as publisher_name", "label", "topic" , "views","evaluations.like",
                "collections", "top", "img", "title", "users.avatar","evaluations.created_at as time"
            ])
            ->toArray();
//        $list = Evaluation::all();
//        return $list;
        if(!$evaluation_list){
            return msg(4,__LINE__);
        }
        $evaluation = new Evaluation();
        $message = $evaluation->isLike_Collection($request,$evaluation_list);
        return msg(0,$message);
        //排序
        /**
         * 标题、内容、标签、话题
         * 标题、内容、标签
         * 标题、内容
         * 标题
         *
         * 内容、标签、话题
         * 内容、标签
         * 内容
         *
         * 标签、话题
         * 标签
         *
         * 话题
         */

//        if('标题中含有'){                 //搜索标题
//            $this->titleSearch();
//        }else if('标题中不含,标签和内容中含有'){     //搜索标签和内容
//            $this->LocSearch();
//        }else if('标题、标签、内容中不含，话题中含有'){                       //搜索话题
//            $this->topicSearch();
//        }else{
//            return msg(11, __LINE__);
//        }
    }

//    //标题搜索
//    private function titleSearch()
//    {
//
//    }
//
//    //标签和内容搜索
//    private function LocSearch()
//    {
//
//    }
//
//    //话题搜索
//    private function topicSearch()
//    {
//
//    }

    //查看评测
    public function get_me_list(Request $request){
        $uid = $request->route('uid');
        $eatest = User::query()->find($uid)->eatest;
        $eatest = array_keys(json_decode($eatest,true));
        $evaluation_list = Evaluation::query()->whereIn('evaluations.id',$eatest)
            ->leftJoin('users','evaluations.publisher','=','users.id')
            ->get([
                "evaluations.id", "users.nickname as publisher_name", "label", "topic" , "views","evaluations.like",
                "collections", "top", "img", "title", "evaluations.created_at as time"
            ])->toArray();

        return msg(0,$evaluation_list);
    }
}
