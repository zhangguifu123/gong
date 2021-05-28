<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


function pushMessage($cid,$title,$content){
    $igt = new \IGeTui(env('HOST'),env('APPKEY'),env('MASTERSECRET'));

    // 透传消息模板
    $template = IGtTransmissionTemplateDemo($title,$content);

    //定义"SingleMessage"
    $message = new \IGtSingleMessage();

    $message->set_isOffline(true);//是否离线
    $message->set_offlineExpireTime(3600*12*1000);//离线时间
    $message->set_data($template);//设置推送消息类型
    $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，2为4G/3G/2G，1为wifi推送，0为不限制推送

    //接收方
    $target = new \IGtTarget();
    $target->set_appId(env('APPID'));
    $target->set_clientId($cid);

    try {
        $rep = $igt->pushMessageToSingle($message, $target);
        var_dump($rep);
        echo ("<br><br>");

    }catch(RequestException $e){
        $requstId =e.getRequestId();
        //失败时重发
        $rep = $igt->pushMessageToSingle($message, $target,$requstId);
        var_dump($rep);
        echo ("<br><br>");
    }
}

//透传消息模板
function IGtTransmissionTemplateDemo($title,$content){
    $listId = [
        'title' => $title,
        'content' => $content,
        'payload' => [
            "push"=> "inner",
            "event"=> "warning",
            "silent"=> false,
        ]
    ];
    $mes = [
        'title' => $title,
        'content' => $content,
        'payload' => [
            "push"=> "inner",
            "event"=> "warning",
            "silent"=> false,
            "data"=> ""
        ]
    ];
    $template =  new \IGtTransmissionTemplate();

    $template->set_appId(env('APPID'));//应用appid
    $template->set_appkey(env('APPKEY'));//应用appkey
    $template->set_transmissionType(1);//透传消息类型
    $template->set_transmissionContent(json_encode($listId));//透传内容

    //注意：如果设备离线（安卓），一定要设置厂商推送，不然接收不到推送（比如华为、小米等等）
    //S.title=的值为推送消息标题，对应5+ API中PushMessage对象的title属性值；
    //S.content=的值为推送消息内容，对应5+ API中PushMessage对象的content属性值；
    //S.payload=的值为推送消息的数据，对应5+ API中PushMessage对象的payload属性值；
    $intent = 'intent:#Intent;action=android.intent.action.oppopush;launchFlags=0x14000000;component=com.sky31.gg/io.dcloud.PandoraEntry;S.UP-OL-SU=true;S.title=标题;S.content=内容;S.payload=数据;end';

    $notify = new \IGtNotify();
    $notify->set_title($title);
    $notify->set_content($content);
    $notify->set_intent($intent);
    $notify->set_type(\NotifyInfo_type::_intent);
    $template->set3rdNotifyInfo($notify);

    //下面这些是苹果需要设置的，只要是ios系统的，都要设置这个，不然离线收不到
    //APN高级推送
    $alertmsg=new \DictionaryAlertMsg();
    $alertmsg->body=$mes['content'];
    $alertmsg->actionLocKey="查看";
    $alertmsg->locKey=$listId['content'];
    $alertmsg->locArgs=array("locargs");
    $alertmsg->launchImage="launchimage";
//        IOS8.2 支持
    $alertmsg->title=$mes['title'];
    $alertmsg->titleLocKey="测试";
    $alertmsg->titleLocArgs=array("TitleLocArg");

    $apn = new \IGtAPNPayload();
    $apn->alertMsg=$alertmsg;
    $apn->badge=0;
    $apn->sound="";
    $apn->add_customMsg("payload","payload");
    $apn->contentAvailable=0;
    $apn->category="ACTIONABLE";
    $template->set_apnInfo($apn);

    return $template;
}



function handleUid(Request $request){
    //获取id
    $uid = Auth::guard('api')->user()->getAuthIdentifier();
    if ($uid == null){
        $uid = $request->header('uid');
    }
    return $uid;
}

function handleStuId(Request $request){
    //获取学号
    try {
        $StuId= JWTAuth::parseToken()->authenticate()->stu_id;
    } catch (TokenExpiredException $e){
        $StuId = $request->header('StuId');
    }
//    $StuId = JWTAuth::parseToken()->authenticate()->stu_id;
//    if ($StuId == null){
//        $StuId = $request->header('StuId');
//    }
    return $StuId;
}

function compressedImage($imgsrc, $imgdst) {
    list($width, $height, $type) = getimagesize($imgsrc);

    $new_width = $width;//压缩后的图片宽
    $new_height = $height;//压缩后的图片高

    if($width >= 600){
        $per = 600 / $width;//计算比例
        $new_width = $width * $per;
        $new_height = $height * $per;
    }

    switch ($type) {
        case 1:
            $giftype = check_gifcartoon($imgsrc);
            if ($giftype) {
                header('Content-Type:image/gif');
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromgif($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //90代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 90);
                imagedestroy($image_wp);
                imagedestroy($image);

                return 0;
            }
            break;
        case 2:
//            header('Content-Type:image/jpeg');
            $image_wp = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromjpeg($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            //90代表的是质量、压缩图片容量大小
            imagejpeg($image_wp, $imgdst, 90);
            imagedestroy($image_wp);
            imagedestroy($image);

            return 0;
            break;
        case 3:
            header('Content-Type:image/png');
            $image_wp = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefrompng($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            //90代表的是质量、压缩图片容量大小
            imagejpeg($image_wp, $imgdst, 90);
            imagedestroy($image_wp);
            imagedestroy($image);

            return 0;
            break;
    }
}
/**
 * 利用三翼借接口验证用户名密码
 * @param $sid
 * @param $password
 * @return mixed
 */
function checkUser($sid, $password) { //登录验证
//    $api_url = "https://api.sky31.com/edu-new/student_info.php";
//    $api_url = $api_url . "?role=" . config("sky31.role") . '&hash=' . config("sky31.hash") . '&sid=' . $sid . '&password=' . urlencode($password);
//    $ch = curl_init();
//    curl_setopt($ch, CURLOPT_URL, $api_url);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    $output = curl_exec($ch);
//    curl_close($ch);
    $api_url = Http::post('https://jwxt.sky31.com/api/student/info', [
        'sid' => $sid,
        'pwd' => $password
    ]);
    return json_decode($api_url->body(), true);
//    return json_decode($output, true);
}

/**
 * 设置返回值
 * @param $code
 * @param $msg
 * @return string
 */
function msg($code, $msg) {
    $status = array(
        0 => '成功',
        1 => '缺失参数',
        2 => '账号密码错误',
        3 => '错误访问',
        4 => '未知错误',
        5 => '其他错误',
        6 => '未登录',
        7 => '重复访问',
        8 => '重复添加',
        9 => '无刷新次数',
        10 => '非本人',
        11 => '目标不存在',
        12 => '图片不和谐',
        13 => 'token已过期',
        14 => 'token未过期'
    );

    $result = array(
        'code' => $code,
        'status' => $status[$code],
        'data' => $msg
    );


    return json_encode($result, JSON_UNESCAPED_UNICODE);
}


/**
 * request数据检查
 * @param $request
 * @param $params
 * @return string
 */
function handleData($request,$params)
{
    //检查参数
    if(!$request->has(array_keys($params))){
        return msg(1, '缺失参数');
    }
    //提取数据
    $data = $request->only(array_keys($params));
    //检查数据
    if(Validator::make($data,$params)->fails()){
        return msg(3, '非法参数');
    }
    return $request;
}
