<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interests extends CustModel
{
	public $timestamps = true;
    protected $table = 'interests';

    protected $casts = [
        'id' => 'string',
        'title' => 'json',
        'transports' => 'json',
        'description' => 'json',
        'bibliography' => 'json',
        'audio' => 'json',
        'prices' => 'json',
        'schedules' => 'json',
    ];

    protected $fillable = ['title','geoloc', 'address', 'image', 'transports', 'description', 'bibliography', 'audio', 'schedules','prices','city_id','par_id','created_at','updated_at'];

    protected $aTranslateVars = ['title','transports','description','bibliography', 'audio', 'schedules', 'prices'];

    public static function getByParcours($par_id) {
        $aInterests = Self::where(['par_uid', '=', $par_id], ['state', '=', true]);
        return $this->sendResponse($oCity->toArray(), null);
    }
}
