<?php

namespace App\Http\Controllers\Api\jwxt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\jwxt\AssociationCode;
use Illuminate\Support\Str;
class AssociationCodeController extends Controller
{
    //创建函数
    private function create(Request $request = null){
        //若有重复，重新创建8位大写字母数字混写随机关联码
        do{
            $code = strtoupper(Str::random($length = 8));
            $associationCode = AssociationCode::query()->where('association_code','=',$code);
        }while($associationCode->get()->toArray() != null);
        $uid = $request->route('uid');
        //插入
        $data = ['uid' => $uid,'association_code' => $code];
        $association = new AssociationCode($data);
        if ($association->save()){
            return msg(0,$data);
        }else{
            return msg(4,__LINE__);
        }
    }
    //通过学号获取关联码
    public function get_association(Request $request){
        $uid = $request->route('uid');
        $association = AssociationCode::query()->where('uid','=',$uid)->get(['uid','association_code'])->toArray();
        if ($association == null){
            return $this->create($request);
        }else{
            //判断是否过期
            $result = $this->judge_time($request);
            if ($result > 180){
                //更新关联码
                $result = $this->update($request);
                if(!is_array($result)){
                    return msg(4,__LINE__);
                }else{
                    return msg(0,$result);
                }
            }else{
                //直接返回关联码
                return msg(0,$association);
            }
        }
    }
    //通过关联码获取学号
    public function get_uid(Request $request){
        $association = $request->route('association');
        $uid = AssociationCode::query()->where('association_code','=',$association)->get(['uid','association_code'])->toArray();
        if ($uid == null){
            return msg(11,__LINE__);
        }else{
            return msg(0,$uid);
        }
    }
    //判断关联码是否过期
    private function judge_time(Request $request){
        $uid = $request->route('uid');
        $association = AssociationCode::query()->where('uid','=',$uid)->get()->toArray();
        //计算具体上次更新关联码的时间差，超过7天更新关联码
        $time = $association[0]['updated_at'];
        $diff =  abs(round((time() - strtotime($time)) / 86400));
        return $diff;
    }

    private function update(Request $request = null){
        //若有重复，重新创建8位大写字母数字混写随机关联码
        do{
            $code = strtoupper(Str::random($length = 8));
            $associationCode = AssociationCode::query()->where('association_code','=',$code);
        }while($associationCode->get()->toArray() != null);
        //更新
        $uid = $request->route('uid');
        $Association = AssociationCode::query()->where('uid','=',$uid);
        $update = $Association->update(['association_code' => $code]);
        if ($update) {
            return $Association->get(['uid','association_code'])->toArray();
        }
        return 4;
    }

}
