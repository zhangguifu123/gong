<?php


namespace App\Model\jwxt;


use Illuminate\Database\Eloquent\Model;

class CountDown extends Model
{
    protected $table='count_down';
    public $timestamps=false;
    protected $guarded = ['id','created_at','updated_at'];
//    protected $fillable = [
//        "uid",
//        "location",
//        "target",
//        "remarks",
//        "end_time",
//        "top"
//    ];


}
