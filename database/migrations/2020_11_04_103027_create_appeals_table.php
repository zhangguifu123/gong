<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('eatest_id')->comment('评测id');
            $table->string('name', 20)->comment('申诉人姓名');
            $table->bigInteger('stu_id')->comment('学号');
            $table->string('phone', 20)->comment('手机号');
            $table->string('content')->comment('申诉原因');
            $table->string('handle')->default('邮箱回执')->comment('处理方式');
            $table->string('remarks')->default('0')->comment('备注');
            $table->boolean('status')->default(0)->comment('是否审核');
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
        Schema::dropIfExists('appeals');
    }
}
