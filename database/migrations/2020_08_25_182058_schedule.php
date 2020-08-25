<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Schedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule',function (Blueprint $table){
            $table->id();

            $table->string('student_id')->comment('学号');
            $table->string('semester')->comment('学期');
            $table->string('week')->comment('周次');
            $table->string('course')->comment('课程');
            $table->string('teacher')->comment('授课老师');
            $table->string('location')->comment('地点');
            $table->string('time')->comment('上课时间');
            $table->string('start_time')->comment('开始时间');
            $table->string('end_time')->comment('截止时间');
            $table->string('weeks')->comment('周次详情');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule');
    }
}
