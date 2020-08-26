<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('info', function (Blueprint $table) {
            $table->id();

            $table->string('name')->comment('姓名');
            $table->string('student_id')->comment('学号');
            $table->string('gender')->comment('性别');
            $table->string('grade')->comment('成绩');
            $table->string('department')->comment('学院');
            $table->string('major')->comment('专业');
            $table->string('class')->comment('班级');
            $table->string('phone')->comment('电话');
            $table->string('qq')->comment('QQ');
            $table->string('email')->comment('邮箱');
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
        Schema::dropIfExists('info');
    }
}
