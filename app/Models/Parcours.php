<?php

namespace App\Models;

use Illuminate\Database\Eloquent\CustModel;

class Parcours extends Model
{
	public $timestamps = true;
    protected $table = 'parcours2';

    protected $casts = [
        'id' => 'string',
    ];

    protected $fillable = ['title','time','audio','description', 'city_id', 'state'];

    public static function getByCity($city_id) {
    	$aParcours = Self::where(['city_id', '=', $city_id], ['state', '=', true]);

    	return $this->sendResponse($oCity->toArray(), null);
    }
}
