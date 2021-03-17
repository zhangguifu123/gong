<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class searchEatestController extends Controller
{
    //模糊搜索
    public function fuzzySearch(Request $request)
    {
        //检查数据格式

        //获取数据


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
}
