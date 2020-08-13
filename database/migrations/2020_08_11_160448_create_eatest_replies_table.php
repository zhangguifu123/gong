<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEatestRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eatest_replies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('comment_id')->index()->unsigned()->comment('评论id');
            $table->bigInteger('fromId')->index()->unsigned()->comment('评论者id');
            $table->string('fromName')->comment('评论者昵称');
            $table->boolean("status")->comment("0已查看 1未查看");
            $table->bigInteger('toId')->comment('被评论者id');
            $table->string('fromAvatar')->comment('评论者头像');
            $table->string("content")->comment("回复内容");
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
        Schema::dropIfExists('eatest_replies');
    }
}
