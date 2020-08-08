<?php

namespace App\Model\Eatest;

use App\Models\Like;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        "publisher", "label", "views", "collections", "like", "unlike", "img", "title", "content", "location", "shop_name", "nickname", "top"
    ];

}
