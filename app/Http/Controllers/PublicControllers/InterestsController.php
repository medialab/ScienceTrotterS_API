<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Interests;
use Database\Mockup\DefaultMockup;
use App\Utils\CheckerUtil;

class InterestsController extends Controller
{
	protected $bAdmin = false;
	protected $sModelClass = 'Interests';

	/**
	 * Récupère le Point le plus Proche d'une Géolocalisation
	 * @param  Request $oRequest [description]
	 * @return [type]            [description]
	 */
	public function closest(Request $oRequest) {
		$geoloc = $oRequest->input('geoloc');
		$sParc = $oRequest->input('parcours');

		// Vérification Du Paramètre
		if (empty($geoloc)) {
			return $this->sendError('No Geoloc in request', [], 400);
		}

		$aGeo = explode(';', $geoloc);
		if (count($aGeo) !== 2 || (float) $aGeo[0] == 0 || (float) $aGeo[1] == 0) {
			return $this->sendError('Geoloc must be a string like "2.564;48.56"', [], 400);
		}

		// Recherche du Point
		$oModel = Interests::closest($aGeo, $sParc);
		if (is_null($oModel)) {
			return $this->sendResponse([]);
		}
		return $this->sendResponse($oModel->toArray($this->bAdmin));
	}

	public function byId($sInterestId) {
		$aData = [];
		if (CheckerUtil::is_uuid_v4($sInterestId)) {
			$aWhereClauses = [
				['state', '=', 'true'],
				['id', '=', $sInterestId]
			];
			
			$aData = Interests::where($aWhereClauses)
				->get()
				->toArray()
			;
		}

		return $this->sendResponse($aData);
	}

}
