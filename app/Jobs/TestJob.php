<?php

namespace App\Jobs;

use App\Models\Coupon;
use App\Models\CouponUser;
use Illuminate\Support\Facades\DB;
use App\Services\AudioProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use App\Exceptions\CommandException;

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
//        $over_time = $this->over_time;
        $use_time = date("Y-m-d H:i:s");

        DB::beginTransaction();
        try {
            //优惠劵数量减一
//            $stock = Coupon::where('store', '=', $store)->where('location','=',$location)->where('value','=',$value)->value('stock');
            $stock = Coupon::find($id)->value('stock');
            $stock -= 1;
//            Coupon::where('store','=',$store)->where('location','=',$location)->where('value','=',$value)->update( ['stock'=>$stock] );
            Coupon::find($id)->update(['stock'=>$stock]);

            //插入已使用优惠劵
            CouponUser::insert(['user'=>$user,'store'=>$store,'location'=>$location,'value'=>$value,
                'use_time'=>$use_time]);
            return CommonException::msg(0,"使用成功");
        } catch(\Exception $e)
        {
            DB::rollback();//事务回滚
            throw CommonException::msg(5,$e->getMessage());
        }

        DB::commit();

    }
}
