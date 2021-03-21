<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
use App\Model\Eatest\Evaluation;
use App\User;
use Illuminate\Http\Request;

class searchEatestController extends Controller
{
    //模糊搜索
    public function fuzzySearch(Request $request)
    {
        //检查数据格式
        $params = [
            'index' => ['string']
        ];
//        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //获取数据
        $index = $request->input('index');
        //拉取所有符合条件的评测
        $list = Evaluation::query()
            ->where('title','like','%' . $index . '%')
            ->orWhere('content','like','%' . $index . '%')
            ->orWhere('label','like','%' . $index . '%')
            ->orWhere('topic', 'like','%' . $index . '%')
            ->get()->toArray();
        if(!$list){
            return msg(4,__LINE__);
        }
        return $list;
        //排序
        if(标题中含有){                 //搜索标题
            $this->titleSearch();
        }else if(标签和内容中含有){     //搜索标签和内容
            $this->LocSearch();
        }else if(话题中含有){                       //搜索话题
            $this->topicSearch();
        }else{
            return msg(11, __LINE__);
        }
    }

    //标题搜索
    private function titleSearch()
    {

    }

    //标签和内容搜索
    private function LocSearch()
    {

    }

    //话题搜索
    private function topicSearch()
    {

    }

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
