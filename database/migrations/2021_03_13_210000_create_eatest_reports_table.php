<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEatestReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eatest_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger("eatestId")->comment("被举报评测/评论id");
            $table->integer("userId")->comment("举报者id");
            $table->integer("targetId")->comment("被举报者id");
            $table->integer("type")->comment("举报类型");
            $table->string("describe")->comment("举报描述/评论");
//            $table->string("reportTime")->comment("举报时间");
            $table->string("reason")->comment("举报理由");
            $table->json("prove")->comment("证明")->nullable();
            $table->integer("status")->default(0)->comment('0 待处理,1 无效举报,2 内容下架');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eatest_reports');
    }
}
