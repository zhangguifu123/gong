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
use Symfony\Component\Config\Definition\Exception\Exception;

class CourseGroupController extends Controller
{
    //创建小组
    public function addGroup(Request $request)
    {
        //检查数据结构
        $params = [
            'Founder' => ['string'],
            'groupName' => ['string'],
            'sharingCode' => ['string']
        ];
        $request = handleData($request,$params);
        if(!is_object($request)){
            return $request;
        }
        //提取数据
        $FounderUid = $request->route('uid');
        $data = $request->only(array_keys($params));
        $member = [$FounderUid];
        $data = $data + ['memberSum' => 1, 'member' => json_encode($member), 'FounderUid' => $FounderUid];
        //创建小组
        $addGroup = CourseGroup::query()->create($data);
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
	    $data = [];
        foreach ($getList as $list){
            if(in_array($uid,json_decode($list->member,true))){
                $data[] = $list;
            }
        }
        $message = ['total' => count($data), 'list' => $data];
        return msg(0,$message);
    }

    //小组信息(关联码获取)
    public function getOneGroup(Request $request)
    {
        //提取数据
        $sharingCode = $request->route('sharingCode');
        //查看加入的小组
        $getList = CourseGroup::query()
            ->where('sharingCode', $sharingCode)
            ->get(['groupName', 'Founder', 'sharingCode'])
            ->toArray();
        if(!$getList){
            return msg(4, __LINE__);
        }
        return msg(0,$getList);
    }


    //修改小组名称
    public function updateGroup(Request $request)
    {
        //检查数据格式
        $params = [
            'groupName' => ['string']
        ];
        $request = handleData($request, $params);
        if (!is_object($request)) {
            return $request;
        }
        //提取数据
        $id = $request->route('id');
        $data = $request->only(array_keys($params));
        //修改
        try {
            $update = CourseGroup::query()->where('id', $id)->update($data);
            return msg(0, __LINE__);
        } catch (Exception $e) {
            return msg(4, __LINE__);
        }

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
        //小组成员数加一
        $courseGroup->increment('memberSum');

        $record = $courseGroup->get('member')->first();

        //加入新成员
        $member = json_decode($record->member,true);
        $member[] = $memberUid;
        $data = ['member' => json_encode($member)];
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

        //删除小组成员
        $courseGroup = CourseGroup::query()->where('id',$groupId);
        if(!$courseGroup){
            return msg(4,__LINE__);
        }
        $record = $courseGroup->get('member')->first();
        //移除成员
        $member = json_decode($record->member,true);
        $member = array_diff($member,[$memberUid]);
        $data = ['member' => $member];
        $deleteMember = $courseGroup->update($data);
        if(!$deleteMember){
            return msg(4,__LINE__);
        }
	//成员数减一
	    $courseGroup->decrement('memberSum');
        return msg(0,__LINE__);
    }


    //获取小组详细信息
    public function getMemberList(Request $request)
    {
        //提取数据
        $id = $request->route('id');
        //获取成员信息
        $courseGroup = CourseGroup::query()->where('id',$id)->get(['id','groupName','memberSum','member','Founder','FounderUid','sharingCode'])->toArray();
        if(!$courseGroup){
            msg(4,__LINE__);
        }
        $memberUid = json_decode($courseGroup[0]['member'],true);
        //获取创建人信息
        foreach ($memberUid as $item) {
            $response = json_decode(Http::get('https://jwxt.sky31.com/api/student/' . $item . '/info')->body(),true)['data'];
            if (!is_array($response)) {
                return msg(3, "个人信息获取失败,请重新登陆");
            }
	    $avatar = (User::query()->where('stu_id', $item)->get('avatar')->toArray())[0]['avatar'];
            $member[] = [
                'uid' => $response['sid'],
                'name' => $response['name'],
                'college' => $response['college'],
                'grade' => substr($item, 2, 2) . "级",
                'avatar' => substr($avatar, 1, strlen($avatar) - 2)
            ];
        }
        $courseGroup[0]['member'] = $member;
        $data = $courseGroup;
        return msg(0,$data);
    }




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
