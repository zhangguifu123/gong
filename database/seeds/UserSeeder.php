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
        DB::table('managers')->insert([
            'name' => '我是超人001',
            'stu_id' => 'root',
            'password' => md5('Sky31666'),
            'level' => 0,
        ]);
    }
}
