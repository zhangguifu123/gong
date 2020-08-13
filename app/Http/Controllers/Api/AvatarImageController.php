<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AvatarImageController extends Controller
{
    //图片上传
    public function upload(Request $request) {
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
        // 如果redis连接失败 中止保存
        try {
            $redis = new Redis();
            $redis->connect('avatar_redis', 6379);
//            print_r("success");
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension(); // 获取后缀

        $allow_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allow_ext)) {
            return msg(3, "非法文件" . __LINE__);
        }
        $name = md5(session('uid') . time() . rand(1, 500));
        $all_name = $name . "." . $ext;
        $result = $file->move(storage_path('app/public/image/'), $all_name);
        if (!$result) {
            return msg(500, "图片保存失败" . __LINE__);
        }
        $pic_url = config("app.url") . "/storage/image/" . $all_name;
        $redis->hSet('avatar_image', $pic_url, time()); // 存储图片上传时间 外部辅助脚本过期后删除
        return msg(0, $pic_url);
    }

    //测试
    public function get(Request $request){
        // 如果redis连接失败 中止保存
        try {
            $redis = new Redis();
            $redis->connect('avatar_redis', 6379);
            print_r("success");
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        print_r($redis->hGetAll('eatest_image'));


    }

    /**
     * /api/image 每天将未使用的图片删除
     */
    public function delete(Request $request) {
        $Storage_files = [];
        $redis_files = [];


        $files = Storage::allFiles();   //遍历存储文件
        if (!$files){
            return msg(5,"文件仓库为空".__LINE__);
        }
        foreach ($files as $file){           //遍历结果去掉前缀
            $test = stripos($file,"jpg");
            if ($test){
                $Storage_replace = str_replace("public/image/","",$file);
                $Storage_files[] = $Storage_replace;
            }
        }

        try {                          //遍历redis
            $redis = new Redis();
            $redis->connect('avatar_db', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        $files = $redis->hkeys("avatar_image");
        foreach ($files as $file){           //遍历结果去掉前缀
            $redis_replace = str_replace("https://test.gong.com/storage/image/","",$file);
            $redis_files[] = $redis_replace;
        }
        print_r($redis_files);

        //删除文件
        $intersection = array_diff($Storage_files,$redis_files); //找出存储但未使用的文件
        $disk = Storage::disk('img');
        foreach ($intersection as $file){   //遍历删除
            $disk->delete($file);
        }

        return msg(0,__LINE__);
    }
}
