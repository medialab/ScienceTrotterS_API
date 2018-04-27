<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{

    public $timestamps = false;
    protected $table = 'items';

    protected $casts = [
        'id' => 'string',
    ];
    protected $primaryKey = "id";

}
