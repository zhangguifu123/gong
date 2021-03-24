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
            $table->bigInteger('toId')->index()->comment('文章作者id');
            $table->bigInteger('fromId')->index()->comment('评论者id');
            $table->boolean("status")->comment("0未查看 1已查看");
            $table->string('fromName')->comment('评论者昵称');
            $table->string('fromAvatar')->comment('评论者头像');
            $table->string("content")->comment("留言内容");
            $table->integer("like")->default(0)->comment("赞数");
            $table->integer('handleStatus')->default(0)->comment('0未处理 1上架 2下架');
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
