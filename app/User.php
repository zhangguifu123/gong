<?php

namespace App;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'focus','focused','countdown','consume','library','status','like','name','nickname', 'stu_id', 'password', 'collection', 'eatest', "remember","avatar"
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setToken($user){
        //自定义的载荷填充
        $customClaims = [
            'iss' => "http://market.sky31.com",
            'uid' => $user->id,
        ];
        //利用JWT工厂类生成根据自定义的载荷生成payload
        $payload = JWTFactory::partialMock() ($customClaims);
        //调用Auth类的encode方法就可以生成token
        $token =  JWTAuth::clearResolvedInstance() ($payload);
        //注意,此处的token是一个类,如何直接添加进response()->json()中将会报错,所以强制转换为String便可以当成字符床正常使用了
        return (string)$token;
    }





    public function info($token)
    {
        return [
            'id'         => $this->id,
            'nickname'   => $this->nickname,
            'stu_id'     => $this->stu_id,
            'collection' => $this->collection,
            'like'       => $this->like,
            'eatest'     => $this->eatest,
            'status'     => $this->status,
            'focus'      => $this->focus,
            'focused'      => $this->focused,
            'countdown'  => $this->countdown,
            'remember'   => $this->remember,
            'avatar'     => $this->avatar,
            "token" => $token
        ];
    }

    public function add_upick($upick_id)
    {
        $upick_list = json_decode($this->upick, true);
        if (!key_exists($upick_id, $upick_list)) {
            $upick_list[$upick_id] = 1;
        }else{
            return false;
        }
        $this->upick = json_encode($upick_list);
        $this->save();
        return true;
    }

    public function del_upick($upick_id)
    {
        $upick_list = json_decode($this->upick, true);
        if (key_exists($upick_id, $upick_list)) {
            unset($upick_list[$upick_id]);
        }else{
            return false;
        }
        $this->upick = json_encode($upick_list);
        $this->save();
        return true;
    }

    public function add_countdown($countdown_id)
    {
        $countdown_list = json_decode($this->countdown, true);
        if (!key_exists($countdown_id, $countdown_list)) {
            $countdown_list[$countdown_id] = 1;
        }else{
            return false;
        }
        $this->countdown = json_encode($countdown_list);
        $this->save();
        return true;
    }

    public function del_countdown($countdown_id)
    {
        $countdown_list = json_decode($this->countdown, true);
        if (key_exists($countdown_id, $countdown_list)) {
            unset($countdown_list[$countdown_id]);
        }else{
            return false;
        }
        $this->countdown = json_encode($countdown_list);
        $this->save();
        return true;
    }

    public function add_eatest($evaluation_id)
    {
        $eatest_list = json_decode($this->eatest, true);
        if (!key_exists($evaluation_id, $eatest_list)) {
            $eatest_list[$evaluation_id] = 1;
        }else{
            return false;
        }
        $this->eatest = json_encode($eatest_list);
        $this->save();
        return true;
    }

    public function del_eatest($evaluation_id)
    {
        $eatest_list = json_decode($this->eatest, true);
        if (key_exists($evaluation_id, $eatest_list)) {
            unset($eatest_list[$evaluation_id]);
        }else{
            return false;
        }
        $this->eatest = json_encode($eatest_list);
        $this->save();
        return true;
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
