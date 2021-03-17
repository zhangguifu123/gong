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
            $table->string('appealResult')->default('未处理')->comment('申诉结果');
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
