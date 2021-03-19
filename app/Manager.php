<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    //
    protected $fillable = [
        'name', 'stu_id', 'password', 'department' , 'level'
    ];
    public function info()
    {
        $level = [
            "0" => "超级管理员",
            "1" => "普通管理员"
        ];

        return [
            'id'     => $this->id,
            'name' => $this->nickname,
            'stu_id' => $this->stu_id,
            'dapartment' => $this->department,
            'level' => $level[$this->level]
        ];
    }
}
