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
        DB::table('managers')->insert([         //管理员
            [
                'name' => '我是超人001',
                'stu_id' => 'Sky31',
                'password' => md5('Sky31666'),
                'department' => '技术开发部',
                'level' => 0,
            ],
            [
                'name' => '张贵妇',
                'stu_id' => '201905190401',
                'password' => md5('Sky31666'),
                'department' => '技术开发部',
                'level' => 0,
            ],
        ]);
        DB::table('users')->insert([            //用户
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
        DB::table('evaluations')->insert([          //评测
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
        DB::table('eatest_comments')->insert([          //评论
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
        DB::table('courses')->insert([              //自定义课表
            [
                'uid' => 201905190401,
                'course' => '高数',
                'location' => '学活209',
                'teacher' => '张贵妇',
                'week' => json_encode([1,2,3]),
                'week_string' => '1-3周',
                'section_start' => 1,
                'end_start' => 2,
                'day' => 1,
            ],
            [
                'uid' => 201905190401,
                'course' => '线代',
                'location' => '学活209',
                'teacher' => '张贵妇',
                'week' => json_encode([1,2,3]),
                'week_string' => '1-3周',
                'section_start' => 3,
                'end_start' => 4,
                'day' => 1,
            ],
            [
                'uid' => 201905190403,
                'course' => 'C语言',
                'location' => '学活209',
                'teacher' => '张贵妇',
                'week' => json_encode([1,2,3]),
                'week_string' => '1-3周',
                'section_start' => 5,
                'end_start' => 6,
                'day' => 1,
            ],
            [
                'uid' => 201905190402,
                'course' => 'Python',
                'location' => '学活209',
                'teacher' => '张贵妇',
                'week' => json_encode([1,2,3]),
                'week_string' => '1-3周',
                'section_start' => 9,
                'end_start' => 11,
                'day' => 1,
            ]
        ]);
        DB::table('course_groups')->insert([        //小组
            'groupName' => '技术开发部',
            'memberSum' => 3,
            'member' => json_encode(['201905190401','201905190402','201905190403']),
            'Founder' => '张贵妇',
            'FounderUid' => '201905190401',
            'sharingCode' => 'SKY314255',
        ]);
        DB::table('info')->insert([            //学生信息
            [
                'sid' => '201905190401',
                'name' => '张贵妇',
                'sex' => '男',
                'college' => '公共管理学院',
                'major' => '信息管理与信息系统',
                'class' => '二班',
            ],
            [
                'sid' => '201905190402',
                'name' => '周舟洲',
                'sex' => '男',
                'college' => '公共管理学院',
                'major' => '信息管理与信息系统',
                'class' => '二班',
            ],
            [
                'sid' => '201905190403',
                'name' => '李以为',
                'sex' => '男',
                'college' => '公共管理学院',
                'major' => '信息管理与信息系统',
                'class' => '三班',
            ],
        ]);
    }
}
