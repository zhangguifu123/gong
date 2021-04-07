<?php

namespace App\Http\Controllers\Api\jwxt;

use App\Http\Controllers\Controller;
use App\Model\jwxt\Course;
use App\Model\jwxt\CourseGroup;
use App\StudentInfo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Psy\Util\Str;

class CourseGroupController extends Controller
{
    //创建小组
    public function addGroup(Request $request)
    {
        //检查数据结构
        $params = [
            'Founder' => ['string'],
//            'FounderUid' => ['integer'],
            'groupName' => ['string'],
            'sharingCode' => ['string']
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $uid = $request->route('uid');       //创建人学号
        $data = $request->only(array_keys($params));
        $member = [];
        $data = $data + ['memberSum' => 1, 'member' => json_encode($member), 'FounderUid' => $uid];
        //创建小组
        $addGroup = CourseGroup::query()->create($data);
//        return $data;
        if($addGroup){
            return msg(0,__LINE__);
        }
        return msg(4,__LINE__);
    }


    //查看小组(我创建的)
    public function getCreatedGroupList(Request $request)
    {
        //提取数据
        $FounderUid = $request->route('uid');
        //查看创建的小组
        $getList = CourseGroup::query()
            ->where('FounderUid',$FounderUid)
            ->orderByDesc('created_at')
            ->get();
        if(!$getList){
            return msg(4,__LINE__);
        }
        $message = ['total' => count($getList), 'list' => $getList];
        return msg(0,$message);

    }

    //查看小组(我加入的)
    public function getJoinedGroupList(Request $request)
    {
        //提取数据
        $uid = $request->route('uid');
        //查看加入的小组
        $getList = CourseGroup::query()
            ->orderByDesc('created_at')
            ->get();
        foreach ($getList as $list){
            if(key_exists($uid,json_decode($list->member,true))){
                $data[] = $list;
            }
        }
        $message = ['total' => count($data), 'list' => $data];
        return msg(0,$message);
    }


    //删除小组
    public function deleteGroup(Request $request)
    {
        //提取数据
        $id = $request->route('id');
        //删除小组
        $delete = CourseGroup::destroy($id);
        if($delete){
            return msg(0,__LINE__);
        }
        return msg(4,__LINE__);
    }


    //加入小组
    public function joinGroup(Request $request)
    {
        //提取数据
        $memberUid = $request->route('uid');
        $sharingCode = $request->route('sharingCode');
        $courseGroup = CourseGroup::query()->where('sharingCode',$sharingCode);
        if(!$courseGroup){
            return msg(4,__LINE__);
        }
        $record = $courseGroup->get('member')->first();

        //加入新成员
//        $member[] = $memberId;
        $member = json_decode($record->member,true);
//        $member[$memberUid] = 1;
        $member[] = $memberUid;
//        return $member;
        $data = ['member' => json_encode($member)];
//        return $data;
        $join = $courseGroup->update($data);
        if($join){
            return msg(0,__LINE__);
        }
        return msg(4,__LINE__);
    }


    //移除小组成员(单个)
    public function deleteGroupMember(Request $request)
    {
        //提取数据
        $groupId = $request->route('id');
        $memberUid = $request->route('uid');           //数组
//        return $memberId;

        //删除小组成员
        $courseGroup = CourseGroup::query()->find($groupId);
        if(!$courseGroup){
            return msg(4,__LINE__);
        }
        $record = $courseGroup->get('member')->first();
        //移除成员
        $member = json_decode($record->member,true);
//        unset($member[$memberUid]);
        $member = array_values(array_diff($member,[$memberUid]));
//        return $member;
        $data = ['member' => $member];
        $deleteMember = CourseGroup::query()->update($data);
        if(!$deleteMember){
            return msg(4,__LINE__);
        }
        return msg(0,__LINE__);
    }


    //获取小组详细信息
    public function getMemberList(Request $request)
    {
        //提取数据
        $id = $request->route('id');
        //获取成员信息
        $courseGroup = CourseGroup::query()->find($id)->get(['id','groupName','memberSum','member','Founder','FounderUid','sharingCode'])->toArray();
        if(!$courseGroup){
            msg(4,__LINE__);
        }
        $FounderUid = $courseGroup[0]['Founder'];
        $memberUid = array_values(array_diff(json_decode($courseGroup[0]['member'],true),[$FounderUid]));
        //获取创建人信息
        $FounderName = DB::table('info')->where('sid',$FounderUid)->get(['sid','name','college'])->toArray();
        if(!$FounderName){
            msg(4,__LINE__);
        }
        //获取成员信息
        $memberName = DB::table('info')->whereIn('sid',$memberUid)->get(['sid as uid','name','college'])->toArray();
        if(!$memberName){
            msg(4,__LINE__);
        }
        $courseGroup[0]['member'] = $FounderName + $memberName;
        $data = $courseGroup;
        return msg(0,$data);
    }


//    //生成空课表
//    public function createEmptyCourse(Request $request){
//        //提取数据
//        $id = $request->route('id');        //小组id
//        $uids = json_decode($request->route('uid'));      //数组
//        //生成课表
//            //获取小组成员
////        $GroupMember = CourseGroup::query()->find($id)->whereIn('')->get('member')->toArray();
////        $member = json_decode($GroupMember[0]['member'],true);
////        return $member;
//            //获取有课成员
//                //获取成员课表
//
//        foreach ($uids as $uid) {
//            $course = Http::get('http://123.57.211.11:10302/api/course/extra/' . $uid);
////        return $course_list;
//            $course_list = json_decode($course->body(),true)['data'];
////        return $course_list;
//            foreach ($course_list as $i => $item) {
//                foreach ($item as $j => $sitem) {
//                    $data[$i][$j][] = $uid;        //有课成员
////                    echo 'j:' . $j;
//                }
////                echo 'i:' . $i . PHP_EOL;
////                echo 'i:' . $i;
//            }
//        }
//
////        return $data;
//        //初始化空课表
//        $groupMemberName = DB::table('info')->whereIn('sid',$uids)->get('name')->toArray();
//        if(!$groupMemberName){
//            msg(4,__LINE__);
//        }
//        foreach ($groupMemberName as $name){
//            $names[] = $name->name;
//        }
//        for ($i=0;$i<=7;$i++) {
//            for ($j=0;$j<=5;$j++) {
//                $list[$i][$j][] = $names;
//            }
//        }
////        return $groupMemberName[0];
////        return $list;
//
//        //获取
//                //获取无课成员
//        $memberName = [];
//        foreach ($data as $key1=>$val){
//            foreach ($val as $key2=>$value){
//                $memberUid = array_values(array_diff($uids,$value));
////                return $memberUid;
//                $groupMemberName = DB::table('info')->whereIn('sid',$memberUid)->get('name')->toArray();
//                if(!$groupMemberName){
//                    msg(4,__LINE__);
//                }
////                return $groupMemberName;
//                //成员学号由姓名替换
////                $memberName = null;
//                foreach ($groupMemberName as $name){
//                    $memberName[] = $name->name;
//                }
//                if($memberName == []){
//                    unset($list[$key1][$key2]);
//                }else{
//                    $data[$key1][$key2] = $memberName;
//                }
////                $list[$key1][$key2] = $memberName;
//                $memberName = [];
//            }
//        }
//        return msg(0,$data);
//    }


    //创建分享码
    public function createSharingCode(Request $request)
    {
            //若有重复，重新创建8位大写字母数字混写随机关联码
        do{
            $code = 'SKY' . str_pad(mt_rand(0,99999),5,"0",STR_PAD_BOTH);
            $sharingCode = CourseGroup::query()->where('sharingCode','=',$code);
        }while($sharingCode->get()->toArray() != null);
        //插入
        $data = ['sharingCode' => $code];
        return msg(0,$data);
    }
}
