<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEatestReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eatest_reviews', function (Blueprint $table) {
            $table->id()->comment('评测id');
            $table->timestamps();
            $table->integer('userId')->comment('用户id');
            $table->json('content')->comment('涉嫌内容');
            $table->integer('type')->comment('待审问题/审核类型 0文案违规 1图片违规');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eatest_reviews');
    }
}
