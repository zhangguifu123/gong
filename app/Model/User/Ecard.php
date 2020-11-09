<?php

namespace App\Model\User;

use Illuminate\Database\Eloquent\Model;

class Ecard extends Model
{
    protected $fillable = ['id','stu_id','name','consume','library'];

}
