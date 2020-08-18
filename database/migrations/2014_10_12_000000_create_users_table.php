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
            $table->string('nickname');
            $table->string('name');
            $table->string('stu_id', 20)->unique();
            $table->string('password', 40)->comment("密码md5");
            $table->json('collection');
            $table->json('like');
            $table->json('publish');
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
