<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Parcours;
use App\Models\Interests;
use App\Models\InterestWay;
use App\Utils\CheckerUtil;

class ParcoursController extends Controller
{
	protected $bAdmin = false;
	protected $sModelClass = 'Parcours';

	public function byId($sParcourId) {
		$aData = [];

		if (CheckerUtil::is_uuid_v4($sParcourId)) {
			$aWhereClauses = [
				['state', '=', 'true'],
				['id', '=', $sParcourId]
			];
			
			$aData = Parcours::where($aWhereClauses)
				->get()
				->toArray()
			;
		}

		return $this->sendResponse($aData);
	}

	/**
	 * Calcule la distance + le time les plus courts pour suivre le parcours
	 * @param  String $parcId Id du parcours
	 * @return Array         [
	 *     'pointCnt' => nombre De points à parcourir
	 *     'distance' => Distance à parcourir en Mètres
	 *     'time' => [
	 *         'string' => Heure Sous Forme: 5h 30min
	 *         'h' => Nombre d'Heures
	 *         'm' => Nombre de Minutes
	 *     ]
	 * ]
	 */
	public function length($parcId) {
		$oParc = Parcours::Where('id', $parcId)->get()->first();
		if (is_null($oParc)) {
			return $this->sendError('Fail To fincd parcours with ID: '.$id, [], 404);
		}


		$aRes = $oParc->getLength();
		return $this->sendResponse($aRes);
	}

	public function trace(Request $oRequest, $parcId) {
		$geoloc = $oRequest->input('geoloc');

		if (!empty($geoloc)) {
			$aGeo = explode(';', $geoloc);
			if (count($aGeo) !== 2 || (float) $aGeo[0] == 0 || (float) $aGeo[1] == 0) {
				return $this->sendError('Geoloc must be a string like "2.564;48.56"', [], 400);
			}
		}
		else{
			$aGeo = false;
		}

		$columns = ['id', 'title', 'geoloc', 'state'];
		$sLang = $oRequest->input('lang');

		$oParcList = Parcours::Where([
			['parcours.id', '=', $parcId],
			['parcours.state', '=', true]
		]);

		if ($sLang) {
			$oParcList->where(function($query) use ($sLang) {
				$query->whereNull('parcours.force_lang')
					  ->orWhere('parcours.force_lang', $sLang)
					  ->orWhere('parcours.force_lang', '')
				;
			});

	    	$oParcList->leftJoin('cities', 'parcours.cities_id', '=', 'cities.id');
	    	
	    	$oParcList->whereNull('cities.id');
			// On Cible la langue
		    $oParcList->orWhere(function($query) use ($sLang){
	            $query->Where('cities.state', true);

	            $query->Where('cities.force_lang', '')
	            	->orWhereNull('cities.force_lang')
	            	->orWhere('cities.force_lang', $sLang)
	            ;
		    });
		}

		$oParc = $oParcList->get()->first();

		if ($aGeo) {
			$oFirst = $oCurrent = Interests::closest($aGeo, $parcId, false, $sLang, $columns);
		}
		else{
			$oFirst = $oCurrent = $oParc->getFirstInterest();
		}

		$aResult = $oParc->getOptimizedTrace($oFirst, $sLang, $columns, $this->bAdmin);

		$aResponse = array_merge($oParc->toArray($this->bAdmin), $aResult);

		return $this->sendResponse($aResponse);
		/*var_dump("==== TESTING RESPONSE ====");
		var_dump($aResponse);
		exit;*/
	}

	public function closest(Request $oRequest, $cityId) {
		$sLang = $oRequest->input('lang');
		$aGeo = $oRequest->getGeoloc();
		$columns = $oRequest->input('columns');
		if (!$aGeo) {
			return $this->sendError('Geoloc must be a string like "2.564;48.56"', [], 400);
		}

		if (!$sLang) {
			return $this->sendError('Lang param is required', [], 400);
		}

		if ($columns && !in_array('id', $columns)) {
			$columns[] = 'id';
		}

		$aResult = Parcours::closest($aGeo, false, $cityId, $sLang, $columns);
		return $this->sendResponse($aResult, 'No result found');
	}
}
