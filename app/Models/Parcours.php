<?php

namespace App\Models;

use App\Utils\ModelUtil;

class Parcours extends ModelUtil
{
    protected $table = 'parcours';

    public $timestamps = true;

    protected $fillable = [
      'id',
      'cities_id',
      'title',
      'time',
      'audio',
      'description',
      'force_lang',
      'state',
      'color',
      'created_at',
      'updated_at'
    ];

    protected $casts = [
        'id' => 'string',
        'title' => 'json',
        'time' => 'json',
        'audio' => 'json',
        'description' => 'json'
    ];

<<<<<<< HEAD
    protected $primaryKey = 'id';
=======
    protected $aTranslateVars = ['title','time','audio','description'];
    protected $fillable = ['title','time','audio','description','color','city_id','state','force_lang','updated_at','created_at'];
>>>>>>> f1fdbfe865596218bbe90ad54ae416eda1f5920f

    protected $aTranslateVars = [
      'title', 
      'time', 
      'audio', 
      'description'
    ];
}
