<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatJwxtTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //所有课表
        Schema::create('all_schedule', function (Blueprint $table) {
            $table->id();
            $table->integer('sid')->comment('学号');
            $table->string('term')->comment('学期');
            $table->string('week')->comment('开课周次');
            $table->string('course')->comment('课程名称');
            $table->string('teacher')->comment('老师名称');
            $table->string('location')->comment('课程地点');
            $table->string('day')->comment('星期');
            $table->string('section_start');
            $table->string('section_end');
            $table->string('section_length')->comment('课程长度');
            $table->string('start_time')->comment('开始时间');
            $table->string('end_time')->comment('结束时间');
            $table->string('weeks')->comment('开课周次');
            $table->string('week_string')->comment('开课周次(详)');
        });
        //考试安排
        Schema::create('exam', function (Blueprint $table) {
            $table->id();
            $table->integer('sid')->comment('学号');
            $table->string('course')->comment('课程名称');
            $table->string('date')->comment('考试时间');
            $table->string('week')->comment('考试周');
            $table->string('term')->comment('学期');
            $table->string('week')->comment('开课周次');

            $table->string('teacher')->comment('老师名称');
            $table->string('location')->comment('课程地点');
            $table->string('day')->comment('星期');
            $table->string('section_start');
            $table->string('section_end');
            $table->string('section_length')->comment('课程长度');
            $table->string('start_time')->comment('开始时间');
            $table->string('end_time')->comment('结束时间');
            $table->string('weeks')->comment('开课周次');
            $table->string('week_string')->comment('开课周次(详)');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
