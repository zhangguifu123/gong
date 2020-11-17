<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AvatarImageController extends Controller
{
    //头像上传
    public function upload(Request $request) {
        //检查文件
        if (!$request->hasFile('image')) {
            return msg(1, "缺失参数" . __LINE__);
        }
        $data = $request->only('image');
        $validator = Validator::make($data, [ // 图片文件小于10M
            'image' => 'max:10240'
        ]);
	if ($validator->fails()) {
            if (config("app.debug")) {
                return msg(1, '非法参数' . __LINE__ . $validator->errors());
            }
            return msg(1, '非法参数' . __LINE__);
        }

        //若没有session 判断remember
        $uid = handleUid($request);

        //删除以前的头像
        $old = User::query()->find($uid)->avatar;
        if ($old){
            $old_avatar = json_decode($old);
            $replace = str_replace(config("app.url")."/storage/avatar/","",$old_avatar);
            $disk = Storage::disk('avatar');
            $disk->delete($replace);
        }

        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension(); // 获取后缀

        $allow_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allow_ext)) {
            return msg(3, "非法文件" . __LINE__);
        }
        $name = md5($uid . time() . rand(1, 500));
        $all_name = $name . "." . $ext;
        $result = $file->move(storage_path('app/public/avatar/'), $all_name);
        if (!$result) {
            return msg(500, "图片保存失败" . __LINE__);
        }
        $pic_url = config("app.url")."/storage/avatar/".$all_name;

        $user = User::query()->find($uid);
        $data = ['avatar' => json_encode($pic_url)];
        if ($user->update($data)){
            return msg(0, $pic_url);
        }else{
            return msg(4,'更新失败'.__LINE__);
        }

    }
}
