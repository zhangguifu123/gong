<?php

namespace App\Http\Controllers\Api\Coupon;

use Illuminate\Http\Request;
use App\Model\Coupon\Coupon;
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

        $img = $request->file('image');
        $store_name = $request->post('store');
        $location = $request->post('location');
        $value = $request->post('value');
        $stock = $request->post('stock');
        $secret_key = $request->post('secret_key');
        $start_time = $request->post('start_time');
        $end_time = $request->post('end_time');
        //         $img->getClientOriginalName();
        try{
           $path = $this->saveImg($img,$store_name,$location);
        }catch(\Exception $e){
            return CommonException::msg(7,$e->getMessage());
        }


        try{
            $coupon = Coupon::where('store','=',$store_name)->where('location','=',$location)->where('value','=',$value)->get();
            if ($coupon->count() > 0) {
                return CommonException::msg(1,"");
            };
        }catch(\Exception $e){
            return CommonException::msg(65534,$e->getMessage());
        }

        try{
          $id = Coupon::insertGetId(['store' => $store_name, 'location' => $location,
                'value' => $value,'stock' => $stock,'image'=>$path ,'secret_key' => md5($secret_key),'start_time' => $start_time,'end_time' => $end_time]);
          return CommonException::msg(0,$id);
        }catch(\Exception $e){
            return CommonException::msg(1,$e);
        }

    }


    public function saveImg(?object $img=null,string $store_name,string $location)
    {

        if ($img !=null){
            $entension = $img->getClientOriginalExtension();
            $newName = $store_name . "_" . $location . "." .$entension;
            if (file_exists('storage/img/' . $newName)){
                unlink('storage/img/' . $newName);
                $r = $img->move('storage/img',$newName);
                $res ="http://159.75.6.240:4396/" . $r;
            }else{
                $r = $img->move('storage/img',$newName);
                $res ="http://159.75.6.240:4396/" . $r;
            }
        }else{
            $res = NULL;
        }
//
        return $res;
    }


    public function updateCoupon(Request $request)
    {
        try{
            $path = $this->saveImg($request->file('image'),$request->post('store'),$request->post('location'));
        }catch(\Exception $e){
            return CommonException::msg(4,$e->getMessage());
        }


        try {
            $coupon = [
                'id' => $request->post('id'),
                'store' => $request->post('store'),
                'location' => $request->post('location'),
                'value' => $request->post('value'),
                'stock' => $request->post('stock'),
                'image' => $path,
                'secret_key' => $request->post('secret_key'),
                'start_time' => $request->post('start_time'),
                'end_time' => $request->post('end_time')
            ];
            Coupon::find($request->get('id'))->update($coupon);
        }catch(Exception $e){
            return CommonException::msg(4,$e->getMessage());
        }
        return CommonException::msg(0,$coupon);
    }


    public function deleteCoupon(Request $request)
    {
        try {
            $res = Coupon::find($request->get('id'))->delete();
            return CommonException::msg(0,$res);
        }catch(Exception $e){
            return CommonException::msg(4,$e->getMessage());
        }
    }

}
