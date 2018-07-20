<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\ModelUtil;

class ListenAudio extends ModelUtil
{
	public $timestamps = true;
    protected $table = 'listen_audio';

    protected $casts = [
        'id' => 'string',
        'lang' => 'string',
        'app_id' => 'string',
        'cont_type' => 'string',
        'cont_id' => 'string',
    ];

    protected $fillable = ['lang','cont_id', 'app_id', 'cont_type', 'created_at', 'updated_at'];

    public static function search($query, $columns, $order=false)  {

    }

    public static function listen($sLang, $phone_id, $cont_id, $cont_type) {

    	$oModel = Self::Where([
            ['lang', '=', $sLang],
            ['cont_id', '=', $cont_id],
            ['app_id', '=', $phone_id],
            ['cont_type', '=', $cont_type]
        ])->get()->first();

    	if (!is_null($oModel)) {
            return true;
        }


        $oModel = new ListenAudio;
        $oModel->lang = $sLang;
        $oModel->app_id = $phone_id;
        $oModel->cont_id = $cont_id;
        $oModel->cont_type = $cont_type;

        $b = $oModel->save();

        if (!$b) {
            return false;
        }

    }
}
