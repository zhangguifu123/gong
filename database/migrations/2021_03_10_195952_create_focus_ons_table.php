<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFocusOnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('focus_ons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('uid')->comment('用户ID');
//            $table->string('nickname')->comment('用户昵称');
            $table->bigInteger('follow_uid')->comment('被关注用户ID');
//            $table->string('follow_name')->comment('被关注名称');
            $table->string('status')->comment('0正常1特殊关注-1黑名单')->default(0);
            $table->boolean('mutual')->comment('0单向1已互相关注')->default(0);
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
        Schema::dropIfExists('focus_ons');
    }
}
