<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cities extends CustModel
{

    public $timestamps = true;
    protected $table = 'cities2';

    protected $fillable = ['label','image','state','geoloc','updated_at','created_at'];
    protected $casts = [
        'id' => 'string',
    ];

    protected $primaryKey = "id";
    protected $aTranslateVars = ['label','state'];

}
