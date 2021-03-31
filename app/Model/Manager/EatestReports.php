<?php

namespace App\Model\Manager;

use Illuminate\Database\Eloquent\Model;

class EatestReports extends Model
{
    //
//    protected $dateFormat = 'Y-m-d';
    protected $guarded = ['id','created_at','updated_at'];

//    protected $dates=['created_at'];
//
////日期格式化
//
//    public function setPostAtAttribute($date)
//
//    {
//
//        $this->attributes['created_at'] = Carbon::createFromFormat('Y-m-d', $date);
//    }


//    const CREATED_AT = 'reportTime';
}
