<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stu_id', 20)->unique();
            $table->string('name');
            $table->string('consume', 20)->default(null)->comment('消费密码');
            $table->string('library', 20)->default(null)->comment('图书馆密码');
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
        Schema::dropIfExists('ecards');
    }
}
