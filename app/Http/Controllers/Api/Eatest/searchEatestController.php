<?php

namespace App\Http\Controllers\Api\Eatest;

use App\Http\Controllers\Controller;
use App\Model\Eatest\Evaluation;
use App\User;
use Illuminate\Http\Request;

class searchEatestController extends Controller
{


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


    //模糊搜索
    public function search(Request $request)
    {
        //获取数据
        $index = $request->route('index');
        $page = $request->route('page');
        $offset = $page * 8 - 8;
        //拉取所有符合条件的评测
        $evaluation_list = Evaluation::query()
            ->limit(8)
            ->offset($offset)
            ->orderByDesc("evaluations.created_at")
            ->where('title', 'like', '%' . $index . '%')      //标题
            ->orwhere('content', 'like', '%' . $index . '%')      //内容
            ->orWhere('label', 'like', '%' . $index . '%')      //标签
            ->orWhere('topic', 'like', '%' . $index . '%')       //话题
            ->whereIn('evaluations.status', [0, 1])
            ->leftJoin('users', 'evaluations.publisher', '=', 'users.id')
            ->get([
                "evaluations.id", "users.nickname as publisher_name", "label", "topic", "views", "evaluations.like",
                "collections", "top", "img", "title", "users.avatar", "evaluations.created_at as time"
            ])
            ->toArray();
        if (!$evaluation_list) {
            return msg(4, __LINE__);
        }
        $message = $this->isLike_Collection($request, $evaluation_list);
        return msg(0, $message);
    }


    //条件搜索
    public function cdSearch(Request $request, $topic = null, $orderBy = null)
    {
        //获取数据
        $index = $request->route('index');
        $topic = $request->route('topic');
        $orderBy = $request->route('orderBy');
        $page = $request->route('page');
        $offset = $page * 8 - 8;
        //拉取所有符合条件的评测
        $evaluation_list = Evaluation::query()
            ->limit(8)
            ->offset($offset)
            ->where('title', 'like', '%' . $index . '%')      //标题
            ->orwhere('content', 'like', '%' . $index . '%')      //内容
            ->orWhere('label', 'like', '%' . $index . '%')      //标签
            ->orWhere('topic', 'like', '%' . $index . '%')       //话题
            ->whereIn('evaluations.status', [0, 1])
            ->leftJoin('users', 'evaluations.publisher', '=', 'users.id')
            ->get([
                "evaluations.id", "users.nickname as publisher_name", "label", "topic", "views", "evaluations.like",
                "collections", "top", "img", "title", "users.avatar", "evaluations.created_at as time"
            ])
            ->toArray();
        if (!$evaluation_list) {
            return msg(4, __LINE__);
        }
        $message = $this->isLike_Collection($request, $evaluation_list);
        return msg(0, $message);
    }






    /** 返回Eatest列表 是否喜欢和收藏
     * @param $request
     * @param $evaluation_list
     */
    private function isLike_Collection($request,$evaluation_list){
        //定义循环内的参数，防止报warning
        $new_evaluation_list = [];
        $authorization = $request->header('Authorization');
        if (isset($authorization) && $authorization !=null){
            $uid = handleUid($request);
        }else{
            $uid = 0;
        }
        //判断是否喜欢and收藏
        foreach ($evaluation_list as $evaluation){
            //判断evaluation_id 是否存在于 user表的 like和collection数组里
            if ($uid != 0){
                $is_like = key_exists($evaluation['id'],json_decode(User::query()->find($uid)->like,true));
                $is_collection = key_exists($evaluation['id'],json_decode(User::query()->find($uid)->collection,true));
            }else{
                $is_like = 0;
                $is_collection = 0;
            }

            //加入两个参数 并生成新数组
            $evaluation += ['is_like' => $is_like,'is_collection' => $is_collection];
            $new_evaluation_list[] = $evaluation;
        }
        $message = ['total' => count($evaluation_list), 'list' => $new_evaluation_list];
        return $message;
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
