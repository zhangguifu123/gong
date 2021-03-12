<?php

namespace App\Model\Eatest;

use Illuminate\Database\Eloquent\Model;

class FocusOn extends Model
{
    //
    protected $fillable = [
        "uid","nickname","follow_uid","follow_name","status","mutual"
    ];
}
