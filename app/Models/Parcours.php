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

    protected $primaryKey = 'id';

    protected $aTranslateVars = [
      'title', 
      'time', 
      'audio', 
      'description'
    ];
}
