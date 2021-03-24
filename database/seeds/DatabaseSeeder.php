<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        DB::table('managers')->insert([
            'name' => '我是超人001',
            'stu_id' => 'Sky31',
            'password' => md5('Sky31666'),
            'department' => '技术开发部',
            'level' => 0,
        ]);
        DB::table('users')->insert([
            'nickname' => '啦啦啦',
            'name' => '张贵妇',
            'stu_id' => 'Sky31',
            'password' => md5('Sky31666'),
            'collection' => '{"1":1}',
            'like' => '{"1":1}',
            'eatest' => '{"1":1}',
            'countdown' =>'{"1":1}',
            'avatar' => '{"1":1}',
            'remember' => 'hahaha',
        ]);
        DB::table('evaluations')->insert([
            'publisher' => 1,
            'nickname' => '啦啦啦',
            'title' => 'hello',
            'label' => '{"1":1}',
//            'topic' => '螺蛳粉',
            'content' => 'hahaha',
            'top' => 0,
            'score' => 1,
            'views' => 0,
            'collections' => 0,
            'like' => 0,
            'status' => 0,
            'img' => '{"1":1}',
        ]);
        DB::table('eatest_comments')->insert([
            'eatest_id' => 1,
            'toId' => 1,
            'fromId' => 1,
            'status' => 0,
            'fromName' => '啦啦啦',
            'fromAvatar' => 'hah',
            'content' => '你好',
            'status' => 1,
            'like' => 3,
            'handleStatus' => 0,
        ]);
    }
}
