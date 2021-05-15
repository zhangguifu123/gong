<?php

namespace App\Http\Controllers\Api\Coupon;

use App\Http\Controllers\Controller;
use App\Model\Coupon\Coupon_Type;
use Illuminate\Http\Request;
use App\Exceptions\CommonException;

class StoreController extends Controller
{
    // 添加商家
    public function addStore(Request $request)
    {
        $store = $request->post('store');
        $location = $request->post('location');
        $image = $request->file('image');
        $remark = json_encode($request->post('remark'),JSON_UNESCAPED_UNICODE);

        //保存图片
        try{
            $path="";
            if(!empty($image)) {
                $entension = $image->getClientOriginalExtension();
                if ($entension !="jpg"&&
                    $entension !="png"){
                    return CommonException::msg(7,"图片格式错误");
                }
                $path = $this->saveImg($image, $store, $location);
            }
        }catch(Exception $e){
            return CommonException::msg(7,$e->getMessage());
        }
        //保存商家
        try{
            $coupon = Coupon_Type::where('store','=',$store)->where('location','=',$location)->get();
            if ($coupon->count() != 0) {
                return CommonException::msg(9,"已存在");
            }
            $save = Coupon_Type::insertGetId(['store'=>$store,"location"=>$location,"images"=>$path,"remark"=>$remark]);
        }catch(Exception $e){
            return CommonException::msg(65534,$e->getMessage());
        }

        if ($save){
            return CommonException::msg(0,$save);
        }else{
            return CommonException::msg(9,"");
        }
    }

    //更新商家
    public function updateStore(Request $request)
    {
        $id = $request->post('id');
        $store = $request->post('store');
        $location = $request->post('location');
        $image = $request->file('image');
        $remark = $request->post('remark');

        //更新图片
        try{
            $path="";
            if(!empty($image)) {
                $entension = $image->getClientOriginalExtension();
                if ($entension !="jpg"&&
                    $entension !="png"){
                    return CommonException::msg(7,"图片格式错误");
                }
                $path = $this->saveImg($image, $store, $location);
            }else{
                $path = Coupon_Type::where('id','=',$id)->value('images');
            }
        }catch(Exception $e){
            return CommonException::msg(7,$e->getMessage());
        }
        //更新商家
        try{
           $update = Coupon_Type::where('id','=',$id)->update(['store'=>$store,"location"=>$location,"images"=>$path,"remark"=>$remark]);
        }catch(Exception $e){
            return CommonException::msg(2,$e->getMessage());
        }

        if ($update){
            return CommonException::msg(0,"");
        }else{
            return CommonException::msg(10,"");
        }
    }

    //
    public function saveImg(?object $img=null,string $store_name,string $location)
    {

        if ($img !=null){
            $entension = $img->getClientOriginalExtension();
            $newName = $store_name . "_" . $location . "." .$entension;
            if (file_exists("storage/img/" . $newName)){
                if (unlink("storage/img/" . $newName)){
                    $r = $img->move("storage/img",$newName);
                    $res ="http://159.75.6.240:10302/" . $r;
                }else{
                    return CommonException::msg('7',"");
                }
            }else{
                $r = $img->move("storage/img",$newName);
                $res ="http://159.75.6.240:10302/" . $r;
            }
        }else{
            $res = NULL;
        }

        return $res;
    }

}
