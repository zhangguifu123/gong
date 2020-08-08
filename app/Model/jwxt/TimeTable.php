<?php

namespace App\Model\jwxt;

use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    protected $fillable = [
        "publisher", "tag", "views", "collections", "like", "unlike", "img", "title", "content", "location", "shop_name", "nickname", "top"
    ];
}
