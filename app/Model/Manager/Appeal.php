<?php

namespace App\Model\Manager;

use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
    //
    protected $fillable = [
        'eatest_id', 'name', 'stu_id', 'phone','content','status','handle','remarks'
    ];
}
