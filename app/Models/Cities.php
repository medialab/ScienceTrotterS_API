<?php

namespace App\Models;

use App\Utils\ModelUtil;

class Cities extends ModelUtil
{
    protected $table = 'cities';

    public $timestamps = true;

    protected $fillable = [
      'id',
      'title',
      'image',
      'geoloc',
      'force_lang',
      'state',
      'created_at',
      'updated_at'
    ];

    protected $casts = [
      'id' => 'string',
      'title' => 'json',
      'geoloc' => 'json'
    ];

    protected $primaryKey = 'id';

    protected $aTranslateVars = [
      'title'
    ];
}
