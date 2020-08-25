<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Exam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam', function (Blueprint $table){
            $table->id();

            $table->string('student_id')->comment('学号');
            $table->string('course')->comment('课程');
            $table->string('date')->comment('日期');
            $table->string('week')->comment('周次');
            $table->string('day')->comment('天');
            $table->string('start_time')->comment('开始时间');
            $table->string('emd_time')->comment('结束时间');
            $table->string('location')->comment('地点');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam');
    }
}
