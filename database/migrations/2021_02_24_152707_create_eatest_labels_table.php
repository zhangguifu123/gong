<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEatestLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eatest_labels', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("labelName",20)->comment("标签名称");
            $table->integer("UsageTime")->default(0)->comment("标签使用次数");
            $table->integer("type")->comment("标签类型");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eatest_labels');
    }
}
