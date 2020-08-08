<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user")->index();
            $table->unsignedBigInteger("evaluation")->comment("被赞/踩帖子id");
            $table->boolean("like")->comment("0踩 1赞");
            $table->boolean("type")->comment("0Eatest 1Upick");
            $table->unique(["user", "evaluation"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
    }
}
