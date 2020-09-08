<?php

namespace App\Http\Controllers\Api\jwxt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function associate_course(Request $request){
        $week_course =[];
        $schedule = [];
        //声明理想数据格式
        $mod = [
            "association" => ["string"]
        ];
        //是否缺失参数
        if (!$request->has(array_keys($mod))){
            return msg(1,__LINE__);
        }
        //检查格式
        $association = $request->input('association');
        if (!is_string($association)){
            return msg(3,__LINE__);
        }
        $uid = DB::table('association_codes')->where('association_code','=',$association)->get(['uid'])->toArray();
        $uid = $uid[0]->uid;
        if (!$uid){
            return msg(11,__LINE__);
        }

        for ($i = 1;$i < 8;$i++){
            //查询周一的课表
                $courses = DB::table('schedule')
                    ->where('sid','=',$uid)
                    ->where('day','=',$i)->get([
                        'course','teacher','location','day','section_start','section_end','section_length','start_time','end_time','weeks as week','week_string'
                    ])->toArray();
//                print_r(json_encode($courses));
                $one_course = [];
                $two_course = [];
                $three_course = [];
                $four_course = [];
                $five_course = [];
                foreach($courses as $j){
                    print_r($j);
                    switch ($j->section_start){
                        case 1:
                            foreach ($one_course as $item){
                                if ($item->course == $j->course && $item->section_start == $j->sectionstart){
                                    break;
                                }
                                $one_course[] = $j;
                                print_r($one_course);
                            }
                            break;
                        case 3:
                            foreach ($two_course as $item){
                                if ($item->course == $j->course && $item->section_start == $j->sectionstart){
                                    break;
                                }
                                $two_course[] = $j;
                            }
                            $two_course[] = $j;
                            break;
                        case 5:
                            foreach ($three_course as $item){
                                if ($item->course == $j->course && $item->section_start == $j->sectionstart){
                                    break;
                                }
                                $three_course[] = $j;
                            }
                            $three_course[] = $j;
                            break;
                        case 7:
                            foreach ($four_course as $item){
                                if ($item->course == $j->course && $item->section_start == $j->sectionstart){
                                    break;
                                }
                                $four_course[] = $j;
                            }
                            $four_course[] = $j;
                            break;
                        case 9:
                            foreach ($five_course as $item){
                                if ($item->course == $j->course && $item->section_start == $j->sectionstart){
                                    break;
                                }
                                $five_course[] = $j;
                            }
                            $five_course[] = $j;
                            break;
                    }
                    print_r(json_encode($one_course));
            }

//                print_r(json_encode($courses));
        }

//        return msg(0,$schedule);
    }

    public function info(Request $request){
        //声明理想数据格式
        $mod = [
            "association" => ["string"]
        ];
        //是否缺失参数
        if (!$request->has(array_keys($mod))){
            return msg(1,__LINE__);
        }
        //检查格式
        $association = $request->input('association');
        if (!is_string($association)){
            return msg(3,__LINE__);
        }
        $uid = DB::table('association_codes')->where('association_code',$association)->uid;
        if (!$uid){
            return msg(11,__LINE__);
        }

        $name = DB::table('users')->where('stu_id',$uid)->name;

        $data = ['std'=>$uid,'name'=>$name,'association'=>$association];
        return msg(0,$data);
    }

    public function empty_course(Request $request)
    {
        $week_course =[];
        $schedule = [];
        //声明理想数据格式
        $mod = [
            "associations" => ["json"]
        ];
        //是否缺失参数
        if (!$request->has(array_keys($mod))){
            return msg(1,__LINE__);
        }
        //获取关联码数组
        $associations = json_decode($request->input('associations'));
        if (!is_array($associations)){
            return msg(3,__LINE__);
        }
        //获取学号数组
        $uids = DB::table('association_codes')->whereIn('association_code',$associations)->get('uid')->toArray();
        if (!$uids){
            return msg(11,'个别'+__LINE__);
        }
        //遍历学号
        foreach ($uids as $item){
            $uid = $item->uid;
            //获取姓名
            $name = DB::table('users')->where('stu_id',$uid)->get()->toArray()[0]->name;
            for ($i = 2;$i < 18;$i++){
                //抓取第i周第j天的课表
                for ($j = 1;$j < 8;$j++){
                    $courses = DB::table('schedule')
                        ->where('sid',$uid)
                        ->where('week',$i)
                        ->where('day',$j)->get([
                            'course','teacher','location','day','section_start','section_end','section_length','start_time','end_time','weeks as week','week_string'
                        ])->toArray();
                    //课表存入第i周第j天
                    $week_course[(string)$j] = $courses;
                }
                //一整周课表存入第i周
                $schedule[(string)$i] = $week_course;
            }
            $user_schedule = array(
                'name' => $name,
                'schedule' => $schedule
            );
            //存入总数组
            $all_user_schedule[] =  $user_schedule;
        }
        return $this->tableFormat(json_encode($all_user_schedule));
    }

    //空课表函数
    private function tableFormat($data)
    {
        $result = array();
        $stuNum = 0;
        $data = json_decode($data,true);
        foreach ($data as $stu)
        {
            $schedule = $stu['schedule'];
            $name = $stu['name'];
            $table = array();
            $table['name'] = $name;
            for($i = 1; $i < 8; $i++)
            {
                for($j = 1; $j < 12; $j++)
                {
                    $table[$i][$j] = 1;
                }
            }
            foreach ($schedule as $keyS => $data)
            {
                for($i = 1; $i < 8; $i++)
                {
                    if(!empty($data[$i]))
                    {
                        for($j = 1; $j < 6; $j++)
                        {
                            if(!empty($data[$i][$j]))
                            {
                                $val = $data[$i][$j];
                                while($val['section_length']--)
                                {
                                    $table[$i][$val['section_start']++] = 0;
                                }
                            }
                        }
                    }
                }
            }
            $result[$stuNum++] = $table;
        }
        return json_encode(array("code" => 0, "message" => "查询成功","data" => $result), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
