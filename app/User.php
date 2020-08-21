<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gulu','like','name','nickname', 'stu_id', 'password', 'collection', 'eatest', "remember","avatar"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function info()
    {
        return [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'stu_id' => $this->stu_id,
            'collection' => $this->collection,
            'like' => $this->like,
            'eatest' => $this->eatest,
            'remember' => $this->remember,
            'avatar' => $this->avatar
        ];
    }

    public function add_eatest($evaluation_id)
    {
        $eatest_list = json_decode($this->eatest, true);
        if (!key_exists($evaluation_id, $eatest_list)) {
            $eatest_list[$evaluation_id] = 1;
        }
        $this->eatest = json_encode($eatest_list);
        $this->save();
    }

    public function del_eatest($evaluation_id)
    {
        $eatest_list = json_decode($this->eatest, true);
        if (key_exists($evaluation_id, $eatest_list)) {
            unset($eatest_list[$evaluation_id]);
        }
        $this->eatest = json_encode($eatest_list);
        $this->save();
    }
    /**
     * @param $evaluation_id
     * @return bool true代表动作成功，否则表名已收藏
     */
    public function add_like($evaluation_id)
    {
        $like_list = json_decode($this->like, true);
        if (!key_exists($evaluation_id, $like_list)) {
            $like_list[$evaluation_id] = 1;
        } else {
            return false;
        }
        $this->like = json_encode($like_list);
        $this->save();

        return true;
    }

    /**
     * @param $evaluation_id
     * @return bool true代表动作成功，否则表名已取消收藏或者未收藏
     */
    public function del_like($evaluation_id)
    {
        $like_list = json_decode($this->like, true);
        if (key_exists($evaluation_id, $like_list)) {
            unset($like_list[$evaluation_id]);
        } else {
            return false;
        }
        $this->like = json_encode($like_list);
        $this->save();

        return true;
    }
    /**
     * @param $evaluation_id
     * @return bool true代表动作成功，否则表名已收藏
     */
    public function add_collection($evaluation_id)
    {
        $collection_list = json_decode($this->collection, true);
        if (!key_exists($evaluation_id, $collection_list)) {
            $collection_list[$evaluation_id] = 1;
        } else {
            return false;
        }
        $this->collection = json_encode($collection_list);
        $this->save();

        return true;
    }

    /**
     * @param $evaluation_id
     * @return bool true代表动作成功，否则表名已取消收藏或者未收藏
     */
    public function del_collection($evaluation_id)
    {
        $collection_list = json_decode($this->collection, true);
        if (key_exists($evaluation_id, $collection_list)) {
            unset($collection_list[$evaluation_id]);
        } else {
            return false;
        }
        $this->collection = json_encode($collection_list);
        $this->save();

        return true;
    }
}
