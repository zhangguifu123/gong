<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\SpecialColumn;
use Illuminate\Http\Request;

class SpecialColumnController extends Controller
{
    //添加专栏
    public function add(Request $request){
        //检查数据格式
        $params = [
            'title' => ['string'],
            'content' => ['string']
        ];
//        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $data = $request->only(array_keys($params));
        //添加专栏
        $add = SpecialColumn::query()->create($data);
        if($add){
            return msg(0,__LINE__);
        }
        return msg(4,__LINE__);
    }


    //查看全部专栏
    public function getList(Request $request){
        //提取数据
        $page = $request->route('page');
        $offset = $page * 5 - 5;
        //分页查看
        $getList = SpecialColumn::query()
            ->limit(5)
            ->offset($offset)
            ->orderByDesc('created_at')
            ->get();
        if(!$getList){
            return msg(4,__LINE__);
        }
        $data = $getList->toArray();
        $message = ['total' => count($data), 'list' => $data];
        return msg(0,$message);
    }


    //查看单个专栏
    public function getListOne(Request $request){
        //提取数据
        $id = $request->route('id');
        //单挑记录查找
        $getListOne = SpecialColumn::query()->find($id)->get();
        if(!$getListOne){
            return msg(4,__LINE__);
        }
        $data = $getListOne->toArray();
        return msg(0,$data);
    }

    //删除专栏
    public function delete(Request $request){
        //提取数据
        $id = $request->route('id');
        //删除
        $delete = SpecialColumn::destroy($id);
        if($delete){
            msg(0,__LINE__);
        }
        msg(4,__LINE__);
    }
}
