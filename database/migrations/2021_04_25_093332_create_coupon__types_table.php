<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon__types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('store')->comment('商家');
            $table->string('location')->comment('地点');
            $table->string('images')->comment('背景图片');
            $table->string('remark')->comment('备注');
//            $table->string('type')->comment('优惠劵类型');
//            $table->string('value')->comment('面额');
//            $table->date('start_time')->comment('开始时间');
//            $table->date('end_time')->comment('结束时间');
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
        Schema::dropIfExists('coupon__types');
    }
}
