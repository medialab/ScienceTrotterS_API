<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{

    public $timestamps = true;
    protected $table = 'cities';

    protected $fillable = ['label','image','state','geoloc'];
    protected $casts = [
        'id' => 'string',
    ];
    protected $primaryKey = "id";

}
