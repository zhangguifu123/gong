<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger("publisher")->comment("发布者id");
            $table->string("nickname")->comment("昵称");
            $table->string("title")->comment("标题");
            $table->json("label")->comment("标签");
            $table->string("topic")->nullable()->comment("参与话题");
            $table->string("content");
            $table->integer( "top")->default(0)->comment("置顶");
//            $table->string("location")->comment("地点");
//            $table->string("shop_name")->nullable()->comment("店名");
            $table->double("score")->index()->default(0)->comment("排序分值");
            $table->integer("views")->default(0)->comment("浏览量");
            $table->integer("collections")->default(0)->comment("被收藏次数");
            $table->integer("like")->default(0)->comment("赞数");
//            $table->integer("unlike")->default(0)->comment("踩数");
            $table->integer("status")->default(0)->comment("0待审核 1上架 2下架");
            $table->json("img")->comment("数组");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evaluations');
    }
}
