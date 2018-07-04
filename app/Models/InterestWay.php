<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Utils\ModelUtil;
use App\Utils\MapApiUtil;

class InterestWay extends ModelUtil
{
	public $timestamps = false;
	protected $primaryKey = 'id';
    protected $table = 'interest_way';

    protected $casts = [
        'int1' => 'string',
        'int2' => 'string',
        'time' => 'float',
        'distance' => 'float'
    ];

    protected $fillable = ['int1','int2','time','distance'];

    public static function byInterest($oInt) {
    	$id = is_string($oInt) ? $oInt : $oInt->id;
    	$oModelList = InterestWay::Where('int1', $id)->orWhere('int2', $id);

    	return $oModelList->get();
    }

    public static function byInterests($oInt1, $oInt2) {
    	$id1 = is_string($oInt1) ? $oInt1 : $oInt1->id;
    	$id2 = is_string($oInt2) ? $oInt2 : $oInt2->id;
    	
		$oModelList = InterestWay::Where(function($query) use ($id1) {
    		$query->where('int1', $id1)
    			->orWhere('int2', $id1)
    		;
    	});

    	$oModelList->where(function($query) use ($id2) {
    		$query->Where('int1', $id2)
    			->orWhere('int2', $id2)
    		;
    	});
    	
    	return $oModelList->get()->first();
    }

    public static function updateByInterest($oInt) {
    	$oParc = $oInt->loadParcours();

    	if (is_null($oParc)) {
    		//var_dump("Parc Is NULL");
    		return [];
    	}

    	$aDistances = [];
    	$mapApi = new MapApiUtil();
    	$aInterests = $oParc->getInterests();

    	foreach ($aInterests as $oInt2) {
    		if ($oInt->id === $oInt2->id) {
    			//var_dump("Same Skip");
    			continue;
    		}
    		elseif(empty($oInt2->geoloc)) {
    			//var_dump("Geo Empty", $oInt2->geoloc);
    			continue;
    		}

    		$aDist = $mapApi->getDistance($oInt, $oInt2);
    		if ($aDist) {
                $aDist = [
                    'time' => $aDist->duration, 
                    'distance' => $aDist->distance
                ];
            }
            else{
        		$aDist = [
        			'time' => -1, 
        			'distance' => -1
        		];
            }


    		$oWay = InterestWay::byInterests($oInt, $oInt2);
    		if (is_null($oWay)) {
    			//var_dump("New Way");
    			$oWay = new InterestWay;
    			$oWay->int1 = $oInt->id;
    			$oWay->int2 = $oInt2->id;
    		}

    		if ($oWay->time !== $aDist['time'] || $oWay->distance !== $aDist['distance']) {
    			$oWay->time = $aDist['time'];
    			$oWay->distance = $aDist['distance'];

    			//var_dump("Save Way");
    			$oWay->save();
    		}

    		$aDistances[$oWay->id] = $oWay;
    		$oInt2->refresh();
    	}

    	return $aDistances;
    }

    public static function deleteByInterest($oInt) {
    	$oWayList = InterestWay::byInterest($oInt);
    	return $oWayList->delete();
    }

	public static function search($search, $columns) { 
		return null; 
	}
}