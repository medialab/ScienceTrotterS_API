<?php


class Parcours 
{
	public $timestamps = true;
    protected $table = 'parcours';

    protected $casts = [
        'id' => 'string',
    ];

    protected $fillable = ['title','time','aud_uid','desc_uid'];

    public static function getByCity($city_id) {
    	$aParcours = Self::where(['city_id', '=', $city_id], ['state', '=', true]);

    	return $this->sendResponse($oCity->toArray(), null);
    }
}
