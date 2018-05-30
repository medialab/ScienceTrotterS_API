<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interests extends Model
{
	public $timestamps = true;
    protected $table = 'interests';

    protected $casts = [
        'id' => 'string',
    ];

    protected $fillable = ['type','location', 'title', 'address', 'schedules', 'prices', 'aud_uid', 'bibli_uid','par_uid'];

    public static function getByParcours($par_id) {
        $aInterests = Self::where(['par_uid', '=', $par_id], ['state', '=', true]);
        return $this->sendResponse($oCity->toArray(), null);
    }
}
