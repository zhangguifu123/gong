<?php

namespace App\Model\Eatest;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $fillable = [
        "collections","nickname", "img", "location", "discount", "publisher"
    ];
}
