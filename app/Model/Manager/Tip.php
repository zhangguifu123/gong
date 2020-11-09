<?php

namespace App\Model\Manager;

use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
//
    protected $fillable = [
        'eatest_id', 'reason','remarks' ,'content','img','status','fromId','toId','reporter'
    ];
}
