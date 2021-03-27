<?php

namespace App\Model\Eatest;

use App\Models\Like;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
//    protected $fillable = [
//        "publisher", "label", "views", "collections", "like","img", "title", "content", "nickname", "top",'status'
//    ];
    protected $guarded = ['id','created_at','updated_at'];


    public function info()
    {
        $publisher_name = User::query()->find($this->publisher)->nickname;

        // 未登录使用默认值
        $is_like = 0;
        $is_collection = 0;
        if (session("login")) {
            $is_like = key_exists($this->id,json_decode(User::query()->find(session("uid"))->like,true));
            $is_collection = key_exists($this->id,json_decode(User::query()->find(session("uid"))->collection,true));
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



