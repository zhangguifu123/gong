<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Coupon\CouponUser;

class DeleteCouponUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:couponusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $time = date('Y-m-d H:i:s');
        $res = CouponUser::where('use_time','<=',$time)->delete();
        echo $res;
        return $res;
    }
}
