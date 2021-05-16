<?php

namespace App\Http\Controllers\Api\Coupon;

use Illuminate\Http\Request;
use App\Model\Coupon\Coupon;
use App\Model\Coupon\Coupon_Type;
use App\Model\Coupon\Coupon_Sec;
use App\Http\Controllers\Controller;
use App\Exceptions\CommonException;

class SaveCouponController extends Controller
{
    /**存储优惠劵
     * @param Request $request
     * @return id
     */
    public function saveCoupon(Request $request)
    {
        //保存优惠劵
        $coupon = array(
            'store'=>$request->post('store'),
            'location'=>$request->post('location'),
            'value'=>$request->post('value'),
            'stock'=>$request->post('stock'),
            'secret_key'=>$request->post('secret_key'),
            'start_time'=>$request->post('start_time'),
            'end_time'=>$request->post('end_time')
        );
        $type = $request->post('type');

        //赋予detail值，文字或图片保存路径
        if ($request->file('detail')){
            $e = $request->file('detail')->getClientOriginalExtension();
            if ($e !="jpg"&&$e !="png") return CommonException::msg(7,"图片格式错误");

            $detail = $this->saveImg($request->file('detail'), $request->post('store'), $request->post('location'));
            $coupon['detail'] = $detail;
        }elseif ($request->post('detail')){
            $coupon['detail'] = $request->post('detail');
        }

        $save = "";
        switch($type){
            case '1':
                $save=$this->CouponTypeOne("save",$coupon);
                break;
            case '2':
                $save=$this->CouponTypeTwo("save",$coupon);
                break;
            case '3':
                $save=$this->CouponTypeThree("save",$coupon);
                break;
        }

        if($save){
            return CommonException::msg(0,$save);
        }else{
            return CommonException::msg(11,"");
        }
    }

    //保存图片
    public function saveImg(?object $img=null,string $store_name,string $location,?string $id=null)
    {

        if ($img !=null){
            $entension = $img->getClientOriginalExtension();
            $newName = $store_name . "_" . $location . "." .$entension;
            if (file_exists("storage/coupon/" . $newName)){
                $link = Coupon_Sec::where("id",'=',$id)->value('images');
                return substr($link,27);
                unlink("storage/coupon/" . $newName);
                $r = $img->move("storage/coupon",$newName);
                $res ="https://gong.sky31.com/" . $r;
            }else{
                $r = $img->move("storage/coupon",$newName);
                $res ="https://gong.sky31.com/" . $r;
            }
        }else{
            $res = NULL;
        }
//
        return $res;
    }


    public function updateCoupon(Request $request)
    {
        $coupon = array(
            'id'=>$request->post('id'),
            'store'=>$request->post('store'),
            'location'=>$request->post('location'),
            'value'=>$request->post('value'),
            'stock'=>$request->post('stock'),
            'secret_key'=>$request->post('secret_key'),
            'start_time'=>$request->post('start_time'),
            'end_time'=>$request->post('end_time')
        );
        $type = $request->post('type');
        $update = "";

        if ($request->file('detail')){
            $e = $request->file('detail')->getClientOriginalExtension();
            if ($e !="jpg"&&$e !="png") return CommonException::msg(7,"图片格式错误");

            $detail = $this->saveImg($request->file('detail'), $coupon['store'], $coupon['location'],$coupon['id']);
            return $detail;
            $coupon['detail'] = $detail;
        }elseif ($request->post('detail')){
            $coupon['detail'] = $request->post('detail');
        }

            switch($type){
                case '1':
                    $update=$this->CouponTypeOne("update",$coupon);
                    break;
                case '2':
                    $update=$this->CouponTypeTwo("update",$coupon);
                    break;
                case '3':
                    $update=$this->CouponTypeThree("update",$coupon);
                    break;
            }

        if($update){
            return CommonException::msg(0,$update);
        }else{
            return CommonException::msg(2,"");
        }
    }


    public function deleteCoupon(Request $request)
    {
        try {
            $type = $request->post('type');
            $coupon = array(
                'id' => $request->post('id')
            );
            $delete = "";

            switch($type){
                case '1':
                    $delete=$this->CouponTypeOne("delete",$coupon);
                    break;
                case '2':
                    $delete=$this->CouponTypeTwo("delete",$coupon);
                    break;
                case '3':
                    $delete=$this->CouponTypeThree("delete",$coupon);
                    break;
            }
        }catch(Exception $e){
            return CommonException::msg(4,$e->getMessage());
        }

        if ($delete){
            return CommonException::msg(0,"");
        }else{
            return CommonException::msg(65533,"");
        }
    }

    public function CouponTypeOne(string $option,array $coupon)
    {
        if ($option == "save"){
            $save = Coupon_fir::insert([
                'store' =>$coupon['store'],
                'location' => $coupon['location'],
                'value' => $coupon['value'],
                'detail' => $coupon['detail'],
                'stock' => $coupon['stock'],
                'secret_key' => $coupon['secret_key'],
                'start_time' => $coupon['start_time'],
                'end_time' => $coupon['end_time']
            ]);
            return $save;
        }elseif($option == "update"){
            $update = Coupon_fir::where('id','=',$coupon['id'])
                ->update([
                    'store' =>$coupon['store'],
                    'location' => $coupon['location'],
                    'value' => $coupon['value'],
                    'detail' => $coupon['detail'],
                    'stock' => $coupon['stock'],
                    'secret_key' => $coupon['secret_key'],
                    'start_time' => $coupon['start_time'],
                    'end_time' => $coupon['end_time']
                ]);
            return $update;
        }elseif($option == "delete"){
            $delete = Coupon_fir::where('id','=',$coupon['id'])
                ->delete();
            return $delete;
        }
    }

    public function CouponTypeTwo(string $option,array $coupon)
    {
        if ($option == "save"){
            $save = Coupon_Sec::insert([
                'store' =>$coupon['store'],
                'location' => $coupon['location'],
                'value' => $coupon['value'],
                'detail' => $coupon['detail'],
                'stock' => $coupon['stock'],
                'secret_key' => $coupon['secret_key'],
                'start_time' => $coupon['start_time'],
                'end_time' => $coupon['end_time']
            ]);
            return $save;
        }elseif($option == "update"){
            $update = Coupon_Sec::where('id','=',$coupon['id'])
                ->update([
                    'store' =>$coupon['store'],
                    'location' => $coupon['location'],
                    'value' => $coupon['value'],
                    'detail' => $coupon['detail'],
                    'stock' => $coupon['stock'],
                    'secret_key' => $coupon['secret_key'],
                    'start_time' => $coupon['start_time'],
                    'end_time' => $coupon['end_time']
                ]);
            return $update;
        }elseif($option == "delete"){
            $delete = Coupon_Sec::where('id','=',$coupon['id'])
                ->delete();
            return $delete;
        }
    }

    public function CouponTypeThree(string $option,array $coupon)
    {
        if ($option == "save"){
            $save = Coupon_Thi::insert([
                'store' =>$coupon['store'],
                'location' => $coupon['location'],
                'value' => $coupon['value'],
                'detail' => $coupon['detail'],
                'stock' => $coupon['stock'],
                'secret_key' => $coupon['secret_key'],
                'start_time' => $coupon['start_time'],
                'end_time' => $coupon['end_time']
            ]);
            return $save;
        }elseif($option == "update"){
            $update = Coupon_Thi::where('id','=',$coupon['id'])
                ->update([
                    'store' =>$coupon['store'],
                    'location' => $coupon['location'],
                    'value' => $coupon['value'],
                    'detail' => $coupon['detail'],
                    'stock' => $coupon['stock'],
                    'secret_key' => $coupon['secret_key'],
                    'start_time' => $coupon['start_time'],
                    'end_time' => $coupon['end_time']
                ]);
            return $update;
        }elseif($option == "delete"){
            $delete = Coupon_Thi::where('id','=',$coupon['id'])
                ->delete();
            return $delete;
        }
    }

}
