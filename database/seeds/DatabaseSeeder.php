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
    }
}
