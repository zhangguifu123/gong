<?php

namespace App\Model\Eatest;

use Illuminate\Database\Eloquent\Model;

class EatestComments extends Model
{
    //可写字段
    protected $guarded = ['id','created_at','updated_at'];
//    protected $fillable = ['eatest_id','toId','fromId','fromName','fromAvatar','content','status','like'];
}
