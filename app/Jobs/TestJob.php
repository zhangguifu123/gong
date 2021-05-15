<?php

namespace App\Jobs;

use App\Model\Coupon\Coupon_fir;
use App\Model\Coupon\Coupon_Sec;
use App\Model\Coupon\Coupon_Thi;
use App\Model\Coupon\CouponUser;
use Illuminate\Support\Facades\DB;
use App\Services\AudioProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use App\Exceptions\CommonException;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $coupon_id;
    private $coupon_type;
    private $user_id;
//    protected $over_time;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$coupon_id,$coupon_type)
    {
        //
        $this->user_id = $user_id;
        $this->coupon_id = $coupon_id;
        $this->coupon_type = $coupon_type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $user_id = $this->user_id;
        $coupon_id = $this->coupon_id;
        $coupon_type = $this->coupon_type;
        $use_time = date("Y-m-d H:i:s");

        DB::beginTransaction();
        try {
            //优惠劵数量减一,使用数量加一
            $update_stock = $this->updateCouponStock($coupon_type, $coupon_id);
//            dd($update_stock);
            //插入已使用优惠劵用户
            $user = CouponUser::insert(['user_id'=>$user_id, 'coupon_id'=>$coupon_id, 'coupon_type'=>$coupon_type, 'use_time'=>$use_time]);
//            echo $user;
        } catch(\Exception $e)
        {
            DB::rollback();//事务回滚
            echo CommonException::msg(5,$e->getMessage());
        }

        DB::commit();

    }

    public function updateCouponStock(string $type, string $id)
    {
        switch($type)
        {
            case "1":
                $stock = Coupon_Sec::where('id','=',$id)->decrement('stock');
                $quantity_used  = Coupon_Sec::where('id','=',$id)->increment('quantity_used');
                break;
            case "2":
                $stock = Coupon_Sec::where('id','=',$id)->decrement('stock');
                $quantity_used  = Coupon_Sec::where('id','=',$id)->increment('quantity_used');
                break;
            case "3":
                $stock = Coupon_Sec::where('id','=',$id)->decrement('stock');
                $quantity_used  = Coupon_Sec::where('id','=',$id)->increment('quantity_used');
                break;
        }
        $coupon=array(
            "stock" => $stock,
            "quantity_used"=>$quantity_used
        );
        return $coupon;
    }

}