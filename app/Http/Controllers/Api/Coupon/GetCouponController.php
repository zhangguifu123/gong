<?php

namespace App\Http\Controllers\Api\Coupon;

use App\Model\Coupon\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Coupon\CouponUser;
use App\Jobs\TestJob;
use Illuminate\Support\Facades\Redis;
//use App\Models\Coupon\Coupon;
use App\Exceptions\CommonException;

class GetCouponController extends Controller
{
    /**获取优惠劵
     * @param Request $request
     * @return
     */
    public function getCoupon(Request $request)
    {
        try{
//            Coupon::
            //找到该商家所有优惠劵
            $today = date('Y-m-d');
            $store_coupons = Coupon::where('store','=',$request->get('store'))->where('location','=',$request->get('location'))->
            where('start_time','<=',$today)->where('end_time','>=',$today)->get();
        }catch(\Exception $e){
            return CommonException::msg(4,$e->getMessage());
        }
        //找到该用户的已使用优惠劵
        try{
            $res = [];

            foreach($store_coupons as $key => $store)
            {

                $user_coupons = CouponUser::where('user','=',$request->get('user'))->where('store','=',$request->get('store'))->
                where('location','=',$request->get('location'))->where('value','=',$store['value'])->get()->first();

                if($user_coupons != null){
                    $store['status'] = false;//已使用
                    $store['use_time'] = $user_coupons['use_time'];
                }else{
                    $store['status'] = true;//未使用
                    $store['use_time'] = "";
                }

                $res[] =$store;
            }
        }catch(\Exception $e){
            return CommonException::msg(4,$e->getMessage());
        }

        if($res == []){
            return CommonException::msg(4,"");
        }
      return CommonException::msg(0,$res);
    }

    public function getStore()
    {
        $today = date('Y-m-d');
        try {
            return  Coupon::select('store','location','image')->
            where('start_time','<=',$today)->where('end_time','>=',$today)->distinct()->get();
        }catch(Exception $e){
            return CommonException::msg(3,$e->getMessage());
        }
    }

    /**使用优惠劵
     *
     *
     */
    public function useCoupon(Request $request)
    {

        try{
            $secret_key =Coupon::find($request->post('id'))->value('secret_key');
            if ($secret_key != md5($request->post('secret_key'))){
                return CommonException::msg(8,"");
            }
            $coupon = Coupon::find($request->post('id'));
            $store = $coupon->store;
            $location = $coupon->location;
            $value = $coupon->value;
//            $over_time = $coupon->time;
        }catch(\Exception $e){
            return CommonException::msg(5,$e->getMessage());
        }

        try{
//            $stock = Coupon::where('store', '=', $request->post('store'))->where('location','=',$request->post('location'))->where('value','=',$request->post('value'))->value('stock');
            $stock = Coupon::find($request->post('id'))->value('stock');
            if ($stock <= 0){
                return CommonException::msg(6,"");
            }

            $res = TestJob::dispatch($request->post('id'),$request->post('user'),$store,$location,$value);
            return CommonException::msg(0,"");
        }catch(\Exception $e){
            return CommonException::msg(5,$e->getMessage());
        }

    }


}
