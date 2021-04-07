<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEatestLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eatest_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user")->index();
            $table->unsignedBigInteger("evaluation")->comment("被赞/踩帖子id");
            $table->boolean("like")->comment("0踩 1赞");
            $table->unique(["user", "evaluation"]);
            $table->Integer("type")->comment("0 Eatest 1Upick");
            $table->Integer("status")->default(0)->comment("0未查看 1已查看");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eatest_likes');
    }
}
