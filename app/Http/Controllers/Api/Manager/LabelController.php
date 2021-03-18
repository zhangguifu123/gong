<?php

namespace App\Http\Controllers\Api\Manager;

use App\Model\Eatest\EatestLabels;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


/**
 * Class LabelController
 * @package App\Http\Controllers\Api\Manager
 * $type = [
 *      0 => "分类",
 *      1 => "地点",
 *      2 => "场景"
 * ]
 */
class LabelController extends Controller
{
    //添加标签
    public function addLabel(Request $request)
    {
        //检查数据格式
        $params = [
            'labelName' => ['string'],
            'type' => ['string']
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $data = $request->only(array_keys($params));
        //添加标签
        $addLabel = EatestLabels::query()->create($data);
        if($addLabel){
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }

    //删除标签
    public function dropLabel(Request $request)
    {
        //检查数据格式

        //提取数据
        $id = $request->route('id');
        //删除标签
        $dropLabel = EatestLabels::destroy($id);
        if($dropLabel){
            $data = ['删除的标签id' => $id];
            return msg(0,$data);
        }
        return msg(4, __LINE__);
    }

    //根据类型查看标签
    public function showLabel(Request $request)
    {
        //提取数据
        $type = $request->route('type');
        //查看标签
        $showLabels = EatestLabels::query()
            ->where('type',$type)
            ->get();
        if(!$showLabels){
            return msg(4, __LINE__);
        }
        $data = $showLabels->toArray();
        $message = [ 'total' => count($data), 'list' => $data];
        return msg(0, $message);
    }
}
