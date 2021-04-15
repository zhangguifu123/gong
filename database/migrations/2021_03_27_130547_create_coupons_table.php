<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('store')->comment('店家名称');
            $table->string('location')->comment('店家位置');
            $table->string('value')->comment('面额');
            $table->integer('stock')->comment('库存');
            $table->json('image')->comment('商家背景');
            $table->string('secret_key', 32)->comment('商家密钥');
            $table->date('start_time')->comment('开始时间');
            $table->date('end_time')->comment('截至时间');
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
        Schema::dropIfExists('coupons');
    }
}
