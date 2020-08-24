<?php

namespace App\Model\jwxt;

use Illuminate\Database\Eloquent\Model;

class AssociationCode extends Model
{
    //
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $fillable = [
        'association_code','uid'
    ];
}
