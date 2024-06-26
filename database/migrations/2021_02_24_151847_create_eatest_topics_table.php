<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEatestTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eatest_topics', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("topicName",40)->comment("话题名称");
            $table->integer("eatestSum")->default(0)->comment("话题发帖数");
            $table->integer("isTop")->default(0)->comment("是否置顶");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eatest_topics');
    }
}
