<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponThisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon__this', function (Blueprint $table) {
            $table->id();
            $table->string('store')->comment('商家');
            $table->string('location')->comment('地点');
            $table->string('value')->comment('面额');
            $table->string('stock')->comment('库存');
            $table->string('secret_key')->comment('密钥');
            $table->date('start_time')->comment('开始时间');
            $table->date('end_time')->comment('结束时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon__this');
    }
}
