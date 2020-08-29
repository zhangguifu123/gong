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
            $table->string('week')->comment('具体周次');
            $table->string('course')->comment('课程名称');
            $table->string('teacher')->comment('老师名称');
            $table->string('location')->comment('课程地点');
            $table->string('day')->comment('星期');
            $table->string('section_start')->comment('开始节次');
            $table->string('section_end')->comment('结束节次');
            $table->string('section_length')->comment('节次长度');
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
            $table->string('day')->comment('学期');
            $table->string('start_time')->comment('开始时间');
            $table->string('end_time')->comment('结束时间');
            $table->string('location')->comment('考试地点');
        });

        //考试安排
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->comment('学号');
            $table->string('name')->comment('姓名');
            $table->string('course')->comment('课程名称');
            $table->string('comp_grade')->comment('成绩');
            $table->string('type')->comment('选修类型');
            $table->string('class_type')->comment('课程类型');
            $table->string('nature_of_test')->comment('考试方式');
            $table->float('credit')->comment('学分');
        });

        //个人信息
        Schema::create('info', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->comment('学号');
            $table->string('name')->comment('姓名');
            $table->string('sex')->comment('性别');
            $table->string('college')->comment('学院');
            $table->string('major')->comment('专业');
            $table->string('class')->comment('班级');
            $table->string('phone')->comment('电话');
            $table->string('qq');
            $table->string('email');
        });
        //当前课表
        Schema::create('schedule',function(Blueprint $table){
            $table->id();
            $table->integer('sid')->comment('学号');
            $table->string('term')->comment('学期');
            $table->string('week')->comment('具体周次');
            $table->string('course')->comment('课程名称');
            $table->string('teacher')->comment('老师名称');
            $table->string('location')->comment('课程地点');
            $table->string('day')->comment('星期');
            $table->string('section_start')->comment('开始节次');
            $table->string('section_end')->comment('结束节次');
            $table->string('section_length')->comment('节次长度');
            $table->string('start_time')->comment('开始时间');
            $table->string('end_time')->comment('结束时间');
            $table->string('weeks')->comment('开课周次');
            $table->string('week_string')->comment('开课周次(详)');
        });
        //总绩点及排名
        Schema::create('gpa',function(Blueprint $table){
            $table->id();
            $table->string('sid')->comment('学号');
            $table->string('term')->comment('学期');
            $table->string('gpa')->comment('总绩点');
            $table->string('avarage_grade')->comment('总成绩');
            $table->string('gpa_class_rank')->comment('班级绩点排名');
            $table->string('gpa_major_rank')->comment('专业绩点排名');
        });
        //过期时间存储
        Schema::create('update_time',function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('sid')->comment('学号');
            $table->date('info')->comment('个人信息');
            $table->date('grades')->comment('成绩');
            $table->date('schedule')->comment('课表');
            $table->date('all_schedule')->comment('所有课表');
            $table->date('exam')->comment('考试安排');
            $table->date('gpa')->comment('绩点及排名');
        });
        //1代表数据在库里，0代表没有，需要使用爬虫
        Schema::create('flag',function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('sid')->comment('学号');
            $table->string('info')->comment('个人信息');
            $table->string('grades')->comment('成绩');
            $table->string('schedule')->comment('课表');
            $table->string('all_schedule')->comment('所有克白哦');
            $table->string('exam')->comment('考试安排');
            $table->string('gpa')->comment('绩点及排名');
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
        Schema::dropIfExists('all_schedule');
        Schema::dropIfExists('exam');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('info');
        Schema::dropIfExists('schedule');
        Schema::dropIfExists('gpa');
        Schema::dropIfExists('update_time');
        Schema::dropIfExists('flag');
    }
}
