<?php

namespace App\Http\Controllers\Api\Coupon;

use App\Model\Coupon\Coupon;
use App\Model\Coupon\CouponUser;
use App\Model\Coupon\Coupon_fir;
use App\Model\Coupon\Coupon_Sec;
use App\Model\Coupon\Coupon_Thi;
use App\Model\Coupon\Coupon_Type;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
//        return "s";
        //获取商家
        if($request->get('store_id') == "all"){
            return Coupon_Type::select('id','store','location','images','remark')->get();
        }

        return 0;
        //找到商家
        $store = Coupon_Type::where('id','=',$request->get('store_id'))->get()->first();
        if (empty($store)){
            return CommonException::msg(4,"优惠劵为空");
        }
        //找到商家所有优惠劵
        $today = date('Y-m-d');
        $user_id = $request->get('user_id');
        $res = [];
        //优惠劵一
        $coupon_one = Coupon_fir::select('id','store','location','value','stock','start_time','end_time')
            ->where('store','=',$store['store'])
            ->where('location','=',$store['location'])
            ->where('start_time','<=',$today)
            ->where('end_time','>=',$today)
            ->get();
        $coupon_one = $this->addStatusToCoupon($coupon_one,$user_id,"1");
        //优惠劵二
        $coupon_two = Coupon_Sec::select('id','store','location','value','detail','stock','start_time','end_time')
            ->where('store','=',$store['store'])
            ->where('location','=',$store['location'])
            ->where('start_time','<=',$today)
            ->where('end_time','>=',$today)
            ->get();
        $coupon_two = $this->addStatusToCoupon($coupon_two,$user_id,'2');
        //优惠劵三
        $coupon_three = Coupon_Thi::select('id','store','location','value','stock','start_time','end_time')
            ->where('store','=',$store['store'])
            ->where('location','=',$store['location'])
            ->where('start_time','<=',$today)
            ->where('end_time','>=',$today)
            ->get();
        $coupon_three = $this->addStatusToCoupon($coupon_three,$user_id,'3');

        $res['type_one'] = $coupon_one;
        $res['type_two'] = $coupon_two;
        $res['type_three'] = $coupon_three;

        return $res;
        if($res == []){
            return CommonException::msg(4,"");
        }
        return CommonException::msg(0,$res);
    }

    public function addStatusToCoupon(object $store_coupons, string $user_id, string $type)
    {
        //匹配优惠劵并添加状态
        $res = [];
        foreach($store_coupons as $key => $value)
        {
            $user_coupons = CouponUser::where('user_id','=',$user_id)
                ->where('coupon_type','=',$type)
                ->where('coupon_id','=',$value['id'])
                ->get()->first();
            if($user_coupons != null){
                $value['status'] = false;//已使用
                $value['use_time'] = $user_coupons['use_time'];
            }else{
                $value['status'] = true;//未使用
                $value['use_time'] = "";
            }
            $res[] = $value;
        }
        return $res;
    }

    /**使用优惠劵
     *
     *
     */
    public function useCoupon(Request $request)
    {
        if ($request->post('coupon_type') == NULL
            ||$request->post('coupon_id') == NULL
            ||$request->post('user_id') == NULL){
            return CommonException::msg(4,"参数缺失");
        }
        $coupon = $this->switchCouponType($request->post('coupon_type'),$request->post('coupon_id'));
        $res = TestJob::dispatch("201905962202","1","2");

        if (empty($empty)){
            return CommonException::msg(4,"优惠劵未找到");
        }
        if ($coupon['secret_key'] != $request->post('secret_key')){
            return CommonException::msg(8,'');
        }
//        try{
//            $stock = Coupon::where('store', '=', $request->post('store'))->where('location','=',$request->post('location'))->where('value','=',$request->post('value'))->value('stock');
        if ($coupon['stock'] <= 0){
            return CommonException::msg(6,"");
        }

        $res = TestJob::dispatch($request->post('user_id'),$coupon['id'],$request->post('coupon_type'));
        return CommonException::msg(0,"");
//        }catch(\Exception $e){
//            return CommonException::msg(5,$e->getMessage());
//        }

    }

    public function switchCouponType(string $type, string $id)
    {
        $coupon = "";
        switch($type)
        {
            case "1":
                $coupon = Coupon_fir::where('id','=',$id)->get()->first();
                break;
            case "2":
                $coupon = Coupon_Sec::where('id','=',$id)->get()->first();
                break;
            case "3":
                $coupon = Coupon_Thi::where('id','=',$id)->get()->first();
                break;
        }
        return $coupon;
    }


}
