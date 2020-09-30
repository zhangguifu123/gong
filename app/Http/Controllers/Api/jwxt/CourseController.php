<?php

namespace App\Http\Controllers\Api\jwxt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
        if (!$uid){
            return msg(11,__LINE__);
        }
        $uid = $uid[0]->uid;

        //http请求
        $response = Http::get('https://campus_data.acver.xyz/api/student/'.$uid.'/course');
        return $response->body();

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
        $uid = DB::table('association_codes')->where('association_code',$association)->get('uid')->toArray();
        if (!$uid){
            return msg(11,__LINE__);
        }
        $uid = $uid[0]->uid;


        $name = DB::table('users')->where('stu_id',$uid)->get('name')->toArray();
        if (!$name){
            return msg(11,__LINE__);
        }
        $name = $name[0]->name;
        $data = ['std'=>$uid,'name'=>$name,'association'=>$association];
        return msg(0,$data);
    }

    public function empty_course(Request $request)
    {
        $week_course =[];
        $schedule = [];
        //声明理想数据格式
        $mod = [
            "associations" => ["string"]
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
    //空课表导出excel函数
    private function TableExcel($data)
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
                        $woGan = $data[$i];
                        foreach ($woGan as $woc) {
                            # code...if(!empty($data[$i][$j]))
                            {

                                $val = $woc;
                                while($val['section_length']--)
                                {
                                    $table[$i][$val['section_start']++] = 0;
                                }
                            }
                        }
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
        $timeTableWoGan = $result;
        $tableWoGan = "<html xmlns:x='urn:schemas-microsoft-com:office:excel'><body>";
        $tableWoGan = $tableWoGan.'<table border="1">';
        $tableWoGan = $tableWoGan.'<tr>
                    <th> </th>
                    <th>周一</th>
                    <th>周二</th>
                    <th>周三</th>
                    <th>周四</th>
                    <th>周五</th>
                    <th>周日</th>
                    <th>周六</th>
                </tr>';
        $i = 0;
// echo $tableWoGan;
        for ($i=1; $i < 12; $i++)//循环生成表格的后面十一行
        {
            $tableWoGan = $tableWoGan.  '<tr>';
            # code...
            //生成每一行的八个格子
            $j = 0;
            $tableWoGan = $tableWoGan."<td>第$i"."节</td>";
            for ($j=1; $j < 8; $j++)
            {
                # code...
                $tableWoGan = $tableWoGan."<td>";
                //将每个人的名字丢到格子里面
                foreach ($timeTableWoGan as $time) {
                    # code...
                    if($time[$j][$i] == 1)
                    {
                        $tableWoGan = $tableWoGan.$time['name']."<br>";
                    }
                }
                $tableWoGan = $tableWoGan."</td>";
            }
            $tableWoGan = $tableWoGan.  '</tr>';
        }
        $tableWoGan = $tableWoGan.   '</table></body></html>';
        $rand = mt_rand(100,9999);
        $rand = (string)$rand;
        $filename = $rand.time()."kkb.excel";
        file_put_contents($filename, $tableWoGan);

    }
}
