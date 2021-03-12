<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nickname')->comment('昵称');
            $table->string('name')->comment('姓名');
            $table->string('stu_id', 20)->unique()->comment('学号');
            $table->string('password', 40)->comment("密码md5");
            $table->json('collection')->comment('我的收藏');
            $table->json('like')->comment('我的喜欢');
            $table->json('eatest')->comment('我的发布');
            $table->boolean('status')->default(1)->comment('禁言');
            $table->json('countdown')->comment('倒计时');
            $table->bigInteger('focused')->default(0)->comment('粉丝量');
            $table->bigInteger('focus')->default(0)->comment('关注数');
            $table->json('avatar')->comment("头像");
            $table->string("remember")->unique();
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
        Schema::dropIfExists('users');
    }
}
