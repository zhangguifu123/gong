<?php

namespace App\Model\jwxt;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //
    protected $fillable = ['uid','course', 'location','teacher','week','week_string','section_length','section_start','end_start','day'];
}
