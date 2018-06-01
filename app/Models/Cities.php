<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cities extends CustModel
{

    public $timestamps = true;
    protected $table = 'cities';

    protected $fillable = ['title','image','state','geoloc','force_lang','updated_at','created_at'];
    
    protected $casts = [
        'id' => 'string',
        'title' => 'json',
    ];

    protected $primaryKey = "id";
    protected $aTranslateVars = ['title'];
}