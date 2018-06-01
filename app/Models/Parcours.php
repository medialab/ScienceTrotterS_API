<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcours extends CustModel
{
	public $timestamps = true;
    protected $table = 'parcours';

    protected $casts = [
        'id' => 'string',
        'title' => 'json',
        'time' => 'json',
        'audio' => 'json',
        'description' => 'json'
    ];

    protected $aTranslateVars = ['title','time','audio','description'];
    protected $fillable = ['title','time','audio','description','city_id','state','force_lang','updated_at','created_at'];

    public static function getByCity($city_id) {
    	$aParcours = Self::where(['city_id', '=', $city_id], ['state', '=', true]);

    	return $this->sendResponse($oCity->toArray(), null);
    }
}
