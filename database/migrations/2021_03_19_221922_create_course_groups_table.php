<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('groupName')->comment('小组名称');
            $table->integer('memberSum')->comment('成员数');
            $table->json('member')->comment('小组成员');
            $table->string('Founder')->comment('创建人姓名');
            $table->string('FounderId')->comment('创建人id');
            $table->string('sharingCode')->comment('分享码');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_groups');
    }
}
