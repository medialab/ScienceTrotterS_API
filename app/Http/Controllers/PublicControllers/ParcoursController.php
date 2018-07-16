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
		$aGeo = explode(';', $geoloc);
		if (count($aGeo) !== 2 || (float) $aGeo[0] == 0 || (float) $aGeo[1] == 0) {
			return $this->sendError('Geoloc must be a string like "2.564;48.56"', [], 400);
		}

		$sLang = $oRequest->input('lang');

		$oParcList = Parcours::Where([
			['id', '=', $parcId],
			['state', '=', true]
		]);

		if ($sLang) {
			$oParcList->where(function($query) use ($sLang) {
				$query->whereNull('force_lang')
					  ->orWhere('force_lang', $sLang)
					  ->orWhere('force_lang', '')
				;
			});
		}

		$oParc = $oParcList->get()->first();

		$columns = ['id', 'title', 'geoloc', 'state'];
		$aPrevious = [];
		$oFirst = $oCurrent = Interests::closest($aGeo, $parcId, $sLang, $columns);
		$oParc->interestsList = [$oCurrent];

		$oFirst->setLang('fr');
		//var_dump("First: ".$oFirst->title);

		$i=0;
		while(!is_null($oNext = InterestWay::closest($oCurrent, $aPrevious, $sLang, true, $columns))) { // Récupération Du point le plus proche
			//var_dump("Current: ".$oCurrent->title);
			$oNext->setLang('fr');


			// Si Désactivé, on continue
			if (!$oNext->state) {
				//var_dump("Skipped: ".$oNext->title);
				$aPrevious[] = $oNext->id;
				continue;
			}

			$aPrevious[] = $oCurrent->id;

			//var_dump("next: ".$oNext->title);

			/*if (empty($oFirst->api_response)) {
				//var_dump("Setting first ")
				$oWay = InterestWay::byInterests($oFirst, $oNext);
				$oFirst->api_response = $oWay->api_response;
			}*/

			//var_dump("Trace: ", is_null($oNext->api_response));
			$oParc->interestsList[] = $oNext->toArray();
			$i++;
			$oCurrent = $oNext;
		}

		//$oParc->interestsList[$i]["api_data"] = null;
		$aResponse = $oParc->toArray();

		$aResponse['interests'] = $oParc->interestsList;
		$aResponse['length'] = $oParc->getLength();
		return $this->sendResponse($aResponse);
	}
}
