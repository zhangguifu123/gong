<?php

namespace App\Model\Eatest;

use Illuminate\Database\Eloquent\Model;

class EatestLikes extends Model
{
    //
    protected $fillable = [
        "user", "evaluation", "like"
    ];
}
