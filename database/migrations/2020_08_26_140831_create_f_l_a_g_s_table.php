<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFLAGSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FLAG', function (Blueprint $table) {
            $table->id();

            $table->string('student_id')->comment('学号');
            $table->string('info')->comment('个人信息');
            $table->string('grade')->comment('成绩');
            $table->string('schedule')->comment('考试安排');
            $table->string('allschedule')->comment('所有安排');
            $table->string('exam')->comment('考试');
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
        Schema::dropIfExists('FLAG');
    }
}
