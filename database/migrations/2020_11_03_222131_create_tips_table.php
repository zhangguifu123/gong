<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('eatest_id')->comment('被举报评测id');
            $table->bigInteger('fromId')->comment('举报人');
            $table->string('reporter', 20)->comment('举报人姓名');
            $table->bigInteger('toId')->comment('被举报人');
            $table->string('reason', 20)->comment('举报理由');
            $table->string('content')->comment('举报描述');
            $table->string('remarks')->default('0')->comment('备注');
            $table->boolean('status')->default(0)->comment('是否审核');
            $table->json('img')->comment('图片');
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
        Schema::dropIfExists('tips');
    }
}
