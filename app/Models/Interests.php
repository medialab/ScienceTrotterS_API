<?php

namespace App\Models;

use App\Utils\ModelUtil;

class Interests extends ModelUtil
{
    protected $table = 'interests';

    public $timestamps = true;

    protected $fillable = [
      'id',
      'cities_id',
      'parcours_id',
      'header_image',
      'title',
      'address',
      'geoloc',
      'schedule',
      'price',
      'audio',
      'transport',
      'audio_script',
      'galery_image',
      'bibliography',
      'force_lang',
      'state',
      'created_at',
      'updated_at'
    ];

    protected $casts = [
        'id' => 'string',
        'title' => 'json',
        'address' => 'json',
        'schedule' => 'json',
        'price' => 'json',
        'audio' => 'json',
        'transport' => 'json',
        'audio_script' => 'json',
        'galery_image' => 'json',
        'bibliography' => 'json',
        'geoloc' => 'json'
    ];

    protected $primaryKey = 'id';

    protected $aTranslateVars = [
      'title', 
      'address', 
      'schedule', 
      'price', 
      'audio', 
      'transport',
      'audio_script',
      'bibliography'
    ];
}
