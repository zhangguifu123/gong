<?php

namespace App\Model\Coupon;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Coupon extends Model
{
//    use HasFactory;


    protected $guarded=['id','created_at','updated_at'];

}
