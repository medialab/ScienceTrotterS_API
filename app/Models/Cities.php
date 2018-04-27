<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{

    public $timestamps = false;
    protected $table = 'cities';

    protected $casts = [
        'id' => 'string',
    ];
    protected $primaryKey = "id";

}
