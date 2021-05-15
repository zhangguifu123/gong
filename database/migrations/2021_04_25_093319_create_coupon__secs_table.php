<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponSecsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon__secs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('store')->comment('商家');
            $table->string('location')->comment('地点');
            $table->string('value')->comment('面额');
//            $table->string('image')->comment('背景图片');
            $table->string('detail')->comment('详情图片');
            $table->integer('stock')->comment('库存');
            $table->string('secret_key')->comment('密钥');
            $table->string('quantity_used')->default('0')->comment('使用数量');
//            $table->string('remarks')->comment('备注');
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
        Schema::dropIfExists('coupon__secs');
    }
}
