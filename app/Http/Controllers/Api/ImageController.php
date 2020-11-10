<?php

namespace App\Http\Controllers\Api;

use App\Helper\imgcompress;
use App\Http\Controllers\Controller;
use App\Traits\CurlTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use \Exception;
use \Redis;

class ImageController extends Controller
{
    //图片上传
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

        // 如果redis连接失败 中止保存
        try {
            $redis = new Redis();
            $redis->connect("gong_redis", 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        $file = $request->file('image');
        $ext = $file->getClientOriginalExtension(); // 获取后缀
        $allow_ext = ['jpg', 'jpeg', 'png', 'gif','HEIC'];

        if (!in_array($ext, $allow_ext)) {
            return msg(3, "非法文件" . __LINE__);
        }
        $name = md5(session('uid') . time() . rand(1, 500));
        $all_name = $name . "." . $ext;
        $pic_url = storage_path('app/public/image/'). $all_name;
        $result = $file->move(storage_path('app/public/image/'), $all_name);
//        $result = Image::make($pic_url)->resize();
        if (!$result) {
            return msg(500, "图片保存失败" . __LINE__);
        }

        //第三方黄图检测
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx95aba4fd9b40e13d&secret=bcd16232a8be911b8bfdabf4fbf77e5c';
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);        //跳过ssl证书验证
        curl_setopt($ch, CURLOPT_URL, $url);                  //设置url
        curl_setopt($ch, CURLOPT_HEADER, 0);                  //不取得返回的头信息
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);          //不直接输出获取到的内容
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);    //设置响应超时时间
        $result = curl_exec($ch);
        if (!mb_check_encoding($result, 'utf-8')) {             // 转换为utf-8编码
            $result = mb_convert_encoding($result,'UTF-8',['ASCII','UTF-8','GB2312','GBK']);
        }
        $responseData = json_decode($result,true);
        $accessToken  =  $responseData['access_token'];
//        print_r($accessToken);
        $pic_url = storage_path('app/public/image/'). $all_name;
//        $pic_url = config("app.url") . "/storage/image/". $all_name;
        // 图片压缩
//        list($width, $height, $type) = getimagesize($pic_url);
//        print_r($width);
//        $image = compressedImage($pic_url,$pic_url);
//        if (!$image){
//            return msg(1,__LINE__);
//        }
        $cfile = new \CURLFile($pic_url);
        $dataLQY = array('media' => $cfile);
        $curl = curl_init();
        $url  = 'https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$accessToken;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $dataLQY);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $result = json_decode($output,true);
        curl_close($curl);
        if ($result['errcode'] != 0){
            return msg(12,__LINE__);
        }

        $pic_url = config("app.url") . "/storage/image/". $all_name;
        $redis->hSet("images", $pic_url, time()); // 存储图片上传时间 外部辅助脚本过期后删除
        return msg(0, $pic_url);
    }

    //测试
    public function get(Request $request){
        //第三方黄图检测
        $response     = Http::get('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx95aba4fd9b40e13d&secret=bcd16232a8be911b8bfdabf4fbf77e5c');
        $responseData = json_decode($response->body(),true);
        $accessToken  =  $responseData['access_token'];
        $response     = Http::post('https://api.weixin.qq.com/wxa/img_sec_check?access_token='.$accessToken, [
            'media' => $request->file('image'),
        ]);

        return $response->body();

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
            $redis->connect('gong_redis', 6379);
        } catch (Exception $e) {
            return msg(500, "连接redis失败" . __LINE__);
        }
        $files = $redis->hkeys("images");
        foreach ($files as $file){           //遍历结果去掉前缀
            $redis_replace = str_replace(config("app.url")."/storage/image/","",$file);
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
