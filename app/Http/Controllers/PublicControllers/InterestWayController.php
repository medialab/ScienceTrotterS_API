<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\InterestWay;

class InterestWayController extends Controller
{
	protected $bAdmin = false;
	protected $sModelClass = 'InterestWay';

	/**
	 * Recherche Par Un Point
	 * @param  Interests|String $sInterestId Le Point Ou Sont ID
	 * @return Json              Les Trajets
	 */
	public function byInterest($sInterestId) {
		$oModelList = InterestWay::byInterest($sInterestId);
		return $this->sendResponse($oModelList->toArray($this->bAdmin));
	}

	/**
	 * Recherche Par Une Paire de Points
	 * @param  Interests|String $sInterestId1 Le Premier Point Ou Sont ID
	 * @param  Interests|String $sInterestId2 Le Deuxième Point Ou Sont ID
	 * @return Json               Le Trajet
	 */
	public function byInterests($sInterestId1, $sInterestId2) {
		$oModelList = InterestWay::byInterests($sInterestId1, $sInterestId2);
		return $this->sendResponse($oModelList->toArray($this->bAdmin));
	}

	/**
	 * Retourne le point d'interet le plus proche de la cible
	 * @param  Interests|String $sInterestId Le Point Ou Sont ID
	 * @param  Request $oRequest    La Requete
	 * @return Json               Le Point
	 */
	public function closest($sInterestId, Request $oRequest) {
		// Récupération des Points à Ignorer
		$sLang = $oRequest->input('lang');
		$bApiData = (bool) $oRequest->input('apiData');
		$aIgnores = $oRequest->input('ignore');

		$oModelList = InterestWay::closest($sInterestId, $aIgnores, $sLang, $bApiData);


		if (is_null($oModelList)) {
			return $this->sendResponse([]);
		}
		
		return $this->sendResponse($oModelList->toArray($this->bAdmin));
	}
}