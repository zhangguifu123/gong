<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('UPDATE_TIME', function (Blueprint $table){
            $table->id();

            $table->dateTime('student_id')->comment('学号');
            $table->dateTime('info')->comment('个人信息');
            $table->dateTime('grade')->comment('成绩');
            $table->dateTime('schedule')->comment('考试安排');
            $table->dateTime('allschedule')->comment('所有安排');
            $table->dateTime('exam')->comment('考试');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('UPDATE_TIME');
    }
}
