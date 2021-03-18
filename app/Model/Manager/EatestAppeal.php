<?php

namespace App\Model\Manager;

use Illuminate\Database\Eloquent\Model;

class EatestAppeal extends Model
{
    //
//    protected $fillable = [
//        'eatest_id', 'name', 'stu_id', 'phone','content','status','handle','remarks'
//    ];
    protected $guarded = ['id','created_at','updated_at'];
}
