<?php

namespace App\Http\Controllers\Api\jwxt;

use App\Http\Controllers\Controller;
use App\Model\Eatest\Evaluation;
use App\Model\jwxt\Course;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

/**
 * Class CourseController
 * @package App\Http\Controllers\Api\jwxt
 *
 */
class CourseController extends Controller
{
    //发布
    public function publish(Request $request){
        //检查数据格式
        $params = [
            "course"      => ["string"],
            "location"    => ["string", "max:50","nullable"],
            "teacher"  => ["string", "max:50","nullable"],
            "week"  => ["json"],
            "section_start"  => ["integer"],
            "end_start"  => ["integer"],
            "section_length" => ['integer'],
            "day"  => ["integer"],
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $data = $request->only(array_keys($params));
        $week = json_decode($request->input('week'));
        $week_string = '第' . $week[0] . '-' . end($week) . '周';
        //获取学号
        //用户id
        $id = handleUid($request);
//        $id = $request->route('uid');
        //学号
        $uid = DB::table('users')->where('id',$id)->get(['stu_id'])->toArray();
        $uid = $uid[0]->stu_id;
//        $uid = 201905190401;
        //加上额外必要数据
        $data = $data + ['uid' => $uid,'week_string' => $week_string];
//        $course = new Course($data);
        $course = Course::query()->insertGetId($data);
        if ($course) {
            $msg = ['添加的课程id' => $course];
            return msg(0,$msg);
        }
        //未知错误
        return msg(4, __LINE__);
    }

    //删除
    public function delete(Request $request)
    {
        //提取数据
        $id = $request->route('id');
        //
        $course = Course::destroy($id);
        if(!$course){
            return msg(4,__LINE__);
        }
        return msg(0, __LINE__);
    }
    //获取课表
    public function get_list(Request $request){
        $uid = $request->route('uid');
//        return $uid;
        $course_list = Course::query()->where('uid',$uid)
            ->get(['id','course', 'location','teacher','week','week_string','section_start','end_start','section_length','day']);
//            ->toArray();
        if(!$course_list){
            return msg(4,__LINE__);
        }
        $response = Http::get('http://159.75.6.240:8080/api/student/'.$uid.'/course');
        $data = json_decode($response->body(),true)['data'];
        foreach ($course_list as $course_item){
            $course_item = json_decode($course_item,true);
            $course_item['week'] = json_decode($course_item['week']);
            $i = $course_item['day'];
            $j = ($course_item['section_start'] + 1) / 2;
            $data[$i][$j][] = $course_item;
        }
//        $message = ['total' => count($course_list), 'list' => $data];
        return msg(0, $data);
    }
    //修改
    public function update(Request $request)
    {
        //检查数据格式
        $params = [
            "course"      => ["string"],
            "location"    => ["string", "max:50","nullable"],
            "teacher"  => ["string", "max:50","nullable"],
            "week"  => ["json"],
            "section_start"  => ["integer"],
            "end_start"  => ["integer"],
            "section_length" => ['integer'],
            "day"  => ["integer"],
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $data = $request->only(array_keys($params));
        //修改
        $course = Course::query()->find($request->route('id'));
        $course = $course->update($data);
        if ($course) {
            return msg(0, __LINE__);
        }
        return msg(4, __LINE__);
    }


    public function associate_course(Request $request)
    {
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
        $response = Http::get('http://159.75.6.240:8080/api/student/'.$uid.'/course');
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
        $name = DB::table('users')->where('stu_id',$uid)->get(['name','avatar']);
        if (!$name){
            return msg(11,__LINE__);
        }
        foreach ($name as $item) {
            $item->avatar = substr($item->avatar, 1, strlen($item->avatar) - 2);
            $item->std = $uid;
            $item->association = $association;
        }
        return msg(0,$name);
    }

    public function empty_course(Request $request){
        //声明理想数据格式
        $all_user_schedule = [];
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
        foreach ($uids as $uid)
        {
            $uid = $uid->uid;
            //获取姓名
            $name = DB::table('users')->where('stu_id',$uid)->get()->toArray()[0]->name;
            //http请求
            $response = Http::get('http://159.75.6.240:8080/api/student/'.$uid.'/course');
            $class =  json_decode($response->body(),true);
            $class = $class['data'];
            $user_schedule = array(
                'name' => $name,
                'schedule' => $class
            );
            //存入总数组
            $all_user_schedule[] =  $user_schedule;
        }
        return $this->tableFormat($all_user_schedule);
    }

    //辅助函数
    private function check($count,$table){
        switch ($count){
            case 1:
                $table['1'] = 0;
                $table['2'] = 0;
                break;
            case 2:
                $table['3'] = 0;
                $table['4'] = 0;
                break;
            case 3:
                $table['5'] = 0;
                $table['6'] = 0;
                break;
            case 4:
                $table['7'] = 0;
                $table['8'] = 0;
                break;
            case 5:
                $table['9'] = 0;
                $table['10'] = 0;
                $table['11'] = 0;
                break;
        }
        return $table;
    }


    //生成空课表
    public function createEmptyCourse(Request $request){
        //提取数据
        $id = $request->route('id');        //小组id
        $uids = json_decode($request->route('uid'));      //数组
        //生成课表
        
        //获取有课成员

        foreach ($uids as $uid) {
            $course = Http::get('http://123.57.211.11:10302/api/course/extra/' . $uid);
            $course_list = json_decode($course->body(),true)['data'];
            foreach ($course_list as $i => $item) {
                foreach ($item as $j => $sitem) {
                    $data[$i][$j][] = $uid;        //有课成员
                }
            }
        }
        //初始化空课表(默认为空)
        foreach ($uids as $uid) {
//            return $uid;
            $response = json_decode(Http::get('http://159.75.6.240:8080/api/student/' . $uid . '/info')->body(),true)['data'];
//            return $response;
            $names[] = $response['name'];
        }

        for ($i=1;$i<=7;$i++) {
            for ($j=1;$j<=5;$j++) {
                foreach ($names as $item) {
                    $list[$i][$j][] = $item;
                }
            }
        }

        //获取
        //获取无课成员
        $memberName = [];
        foreach ($data as $key1=>$val){
            foreach ($val as $key2=>$value){
                $memberUid = array_values(array_diff($uids,$value));
                $groupMemberName = DB::table('info')->whereIn('sid',$memberUid)->get('name')->toArray();
                if(!$groupMemberName){
                    msg(4,__LINE__);
                }
                //成员学号由姓名替换
                foreach ($groupMemberName as $name){
                    $memberName[] = $name->name;
                }
                if($memberName == []){
                    unset($list[$key1][$key2]);
                }else{
                    $list[$key1][$key2] = $memberName;
                }
                $memberName = [];
            }
        }
        return msg(0,$list);
    }








    //空课表函数
    private function tableFormat($data)
    {
        $result = array();
        $stuNum = 0;
        foreach ($data as $stu)
        {
            $course = $stu['schedule'];
            $name = $stu['name'];
            $stuEmptyCourse = array();
            $stuEmptyCourse['name'] = $name;
            for($i = 1; $i < 8; $i++)
            {
                for($j = 1; $j < 12; $j++)
                {
                    $stuEmptyCourse[$i][$j] = 1;
                }
            }
            foreach ($course as $i => $dayCourse)
            {
                    foreach ($dayCourse as $key => $value)
                    {
                        $stuEmptyCourse[$i] = $this->check($key,$stuEmptyCourse[$i]);
                    }

            }
            $result[$stuNum++] = $stuEmptyCourse;
        }
        return msg(0,$result);
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
