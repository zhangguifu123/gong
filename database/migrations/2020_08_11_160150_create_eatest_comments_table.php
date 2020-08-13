<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEatestCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eatest_comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('eatest_id')->index()->comment('文章id');
            $table->bigInteger('fromId')->index()->comment('评论者id');
            $table->boolean("status")->comment("0已查看 1未查看");
            $table->string('fromName')->comment('评论者昵称');
            $table->string('fromAvatar')->comment('评论者头像');
            $table->string("content")->comment("留言内容");
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
        Schema::dropIfExists('eatest_comments');
    }
}
