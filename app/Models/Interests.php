<?php

namespace App\Models;

use App\Utils\ModelUtil;

class Interests extends ModelUtil
{
    protected $table = 'interests';
    protected $userStr = 'le point d\'intérêt';

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
        'cities_id' => 'string',
        'parcours_id' => 'string',
        'title' => 'json',
        'address' => 'json',
        'geoloc' => 'json',
        'schedule' => 'json',
        'price' => 'json',
        'audio' => 'json',
        'transport' => 'json',
        'audio_script' => 'json',
        'gallery_image' => 'json',
        'bibliography' => 'json',
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
      'bibliography',
    ];

    protected $aUploads = ['header_image', 'audio', 'gallery_image'];
}
