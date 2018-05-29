<?php

namespace App\Models;

use Illuminate\Database\Eloquent\CustModel;

class Cities extends Model
{

    public $timestamps = true;
    protected $table = 'cities2';

    protected $fillable = ['label','image','state','geoloc'];
    protected $casts = [
        'id' => 'string',
    ];

    protected $primaryKey = "id";
    protected $aTranslateVars = ['label','state'];

}
