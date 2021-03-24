<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'nickname' => '啦啦啦',
            'name' => '张贵妇',
            'stu_id' => 'Sky31',
            'password' => md5('Sky31666'),
            'collection' => '{1:1}',
            'like' => '{1:1}',
            'eatest' => '{1:1}',
            'countdown' =>'{1:1}',
            'avatar' => '{1:1}',
            'remember' => 'hahaha',
        ]);
    }
}
