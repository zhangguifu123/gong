<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Grades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grades', function (Blueprint $table){
            $table->id();

            $table->string('name')->comment('姓名');
            $table->string('student_id')->comment('学号');
            $table->string('course')->comment('课程');
            $table->string('grade')->comment('成绩');
            $table->string('category')->comment('类别');
            $table->string('nature_of_course')->comment('学科');
            $table->string('semester')->comment('学期');
            $table->string('nature_of_test')->comment('学科类别');
            $table->string('credits')->comment('学分');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grades');
    }
}
