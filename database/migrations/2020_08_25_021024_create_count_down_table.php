<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateCountDownTable extends Migration {

    public function up()
    {
        Schema::create('count_down', function (Blueprint $table) {
            $table->id();
            $table->integer('uid');
            $table->string('location',20);
            $table->string('target',50);
            $table->string('remarks',50);
            $table->time('end_time');
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
