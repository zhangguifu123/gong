<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string("uid")->comment("学生学号");
            $table->string('course')->comment('课程');
            $table->string('location')->nullable()->comment('地点');
            $table->string('teacher')->nullable()->comment('老师');
            $table->json('week')->comment('周次(详)');
            $table->string('week_string')->comment('周次');
//            $table->string('section_length')->comment('课时长度');
            $table->integer('section_start')->comment('开始小节');
            $table->integer('end_start')->comment('结束小节');
            $table->integer('day')->comment('周几');
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
        Schema::dropIfExists('courses');
    }
}
