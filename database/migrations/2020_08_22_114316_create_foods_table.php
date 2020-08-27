<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('foods', function (Blueprint $table) {

            $table->id();
            $table->string('publisher')->comment('发布者')->index();
            $table->string('nickname')->comment('美食名称');
            $table->string('location')->comment('美食位置');
            $table->string('discount')->comment('折扣信息');
            $table->string('img')->comment('图片链接');
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
        Schema::dropIfExists('foods');
    }
}
