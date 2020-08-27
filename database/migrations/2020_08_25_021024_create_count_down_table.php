<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateCountDownTable extends Migration {

    public function up()
    {
        Schema::create('count_down', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->index()->comment('学生id');
            $table->string('location',20)->comment('地点');
            $table->string('target',50)->comment('目标');
            $table->string('remarks',50)->comment('备注');
            $table->time('end_time')->comment('截止日期');
            $table->integer("top")->default(0)->comment("置顶");
            $table->timestamp('time')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('count_down');
    }

}
