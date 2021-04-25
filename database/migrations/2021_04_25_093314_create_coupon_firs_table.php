<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponFirsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_firs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('store')->comment('店家名称');
            $table->string('location')->comment('店家位置');
            $table->string('value')->comment('面额');
            $table->integer('stock')->comment('库存');
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
        Schema::dropIfExists('coupon_firs');
    }
}
