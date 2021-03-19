<?php

namespace App\Model\Eatest;

use Illuminate\Database\Eloquent\Model;

class EatestTopics extends Model
{
    //不允许批量赋值
    protected $guarded = ['id','created_at','updated_at'];

}
