<?php

namespace App\Jobs;

use App\Model\Coupon\Coupon;
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
    protected $id;
    protected $user;
    protected $store;
    protected $location;
    protected $value;
//    protected $over_time;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id,$user,$store,$location,$value)
    {
        //
        $this->id = $id;
        $this->user = $user;
        $this->store = $store;
        $this->location = $location;
        $this->value = $value;
//        $this->over_time = $over_time;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $id = $this->id;
        $user = $this->user;
        $store = $this->store;
        $location = $this->location;
        $value = $this->value;
        $use_time = date("Y-m-d H:i:s");

        DB::beginTransaction();
        try {
            //优惠劵数量减一
            $coupon = Coupon::find($id);
            $stock = $coupon->stock;
            $stock -= 1;
            Coupon::find($id)->update(['stock'=>$stock]);
            //插入已使用优惠劵
            CouponUser::insert(['user'=>$user,'store'=>$store,'location'=>$location,'value'=>$value,
                'use_time'=>$use_time]);
        } catch(\Exception $e)
        {
            DB::rollback();//事务回滚
            throw CommonException::msg(5,$e->getMessage());
        }

        DB::commit();

    }
}