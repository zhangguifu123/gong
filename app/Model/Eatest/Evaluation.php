<?php

namespace App\Model\Eatest;

use App\Models\Like;
use App\User;
use http\Env\Request;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Facades\JWTAuth;

class Evaluation extends Model
{
//    protected $fillable = [
//        "publisher", "label", "views", "collections", "like","img", "title", "content", "nickname", "top",'status'
//    ];
    protected $guarded = ['id','created_at','updated_at'];


    /** 返回Eatest列表 是否喜欢和收藏
     * @param $request
     * @param $evaluation_list
     */
    public function isLike_Collection($request,$evaluation_list){
        //定义循环内的参数，防止报warning
        $new_evaluation_list = [];
        $authorization = $request->header('Authorization');
        if (isset($authorization) && $authorization !=null){
            $uid = handleUid($request);
        }else{
            $uid = 0;
        }
        //判断是否喜欢and收藏
        foreach ($evaluation_list as $evaluation){
            //判断evaluation_id 是否存在于 user表的 like和collection数组里
            if ($uid != 0){
                $is_like = key_exists($evaluation['id'],json_decode(User::query()->find($uid)->like,true));
                $is_collection = key_exists($evaluation['id'],json_decode(User::query()->find($uid)->collection,true));
            }else{
                $is_like = 0;
                $is_collection = 0;
            }

            //加入两个参数 并生成新数组
            $evaluation += ['is_like' => $is_like,'is_collection' => $is_collection];
            $new_evaluation_list[] = $evaluation;
        }
        $message = ['total' => count($new_evaluation_list), 'list' => $new_evaluation_list];
        return $message;
    }

    /** 返回单篇信息 */
    public function info($uid)
    {
        $publisher_name = User::query()->find($this->publisher)->nickname;

        // 未登录使用默认值
        if ($uid != 0){
            $is_like = key_exists($this->id,json_decode(User::query()->find($uid)->like,true));
            $is_collection = key_exists($this->id,json_decode(User::query()->find($uid)->collection,true));
        }else{
            $is_like = 0;
            $is_collection = 0;
        }



        return [
            "id" => $this->id,
            "publisher" => $this->publisher,
            "publisher_name" => $publisher_name,
            "label" => $this->label,
            "topic" => $this->topic,
            "views" => $this->views,
            "like" => $this->like,
            "collections" => $this->collections,
            "top" => $this->top,
            "img" => $this->img,
            "title" => $this->title,
            "content" => $this->content,
            "is_like" => $is_like,
            "is_collection" => $is_collection,
            "time" => date_format($this->created_at, "Y-m-d H:i:s")
        ];
    }
}



