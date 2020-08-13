<?php

namespace App\Model\Eatest;

use Illuminate\Database\Eloquent\Model;

class EatestReplies extends Model
{

    //可写字段
    protected $fillable = ['fromId', 'comment_id', 'content','fromName','toId','fromAvatar','status'];
}
