<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEatestAppealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eatest_appeals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('eatestId')->comment('评测id');
            $table->string('userName', 20)->comment('申诉人姓名');
            $table->string('type')->comment('申诉类型');
            $table->string('content')->comment('申诉内容');
            $table->string('describe')->comment('申诉描述');
            $table->integer('status')->default(0)->comment('0 待处理,1 无效申诉,2 内容还原');
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
        Schema::dropIfExists('eatest_appeals');
    }
}
