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
            /**
             * `name` varchar(40) COLLATE utf8mb4_general_ci NOT NULL COMMENT '美食名称',
            `location` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT '美食位置',
            `discount` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '折扣信息',
            `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '图片位置',
            `publisher` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '发布者',
            `pubdate` date DEFAULT NULL COMMENT '发布时间'
             */
            $table->id();
            $table->string('nickname')->comment('美食名称');
            $table->string('location')->comment('美食位置');
            $table->string('discount')->comment('折扣信息');
            $table->string('img')->comment('图片链接');
            $table->string('publisher')->comment('发布者');
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
