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
		$sLang = $oRequest->input('lang');
		$sCity = $oRequest->input('city');
		$geoloc = $oRequest->input('geoloc');
		$sParc = $oRequest->input('parcours');
		$columns = $oRequest->input('columns');

		if (!$sLang) {
			return $this->sendError('Lang param is required', [], 400);
		}

		// Vérification Du Paramètre
		if (empty($geoloc)) {
			return $this->sendError('No Geoloc in request', [], 400);
		}

		$aGeo = explode(';', $geoloc);
		if (count($aGeo) !== 2 || (float) $aGeo[0] == 0 || (float) $aGeo[1] == 0) {
			return $this->sendError('Geoloc must be a string like "2.564;48.56"', [], 400);
		}


		// Recherche du Point
		$oFirst = Interests::closest($aGeo, $sParc, $sCity, $sLang, $columns);
		if (is_null($oFirst)) {
			return $this->sendResponse(false, 'Not found');
		}

		if ($sParc) {			
			$aResult = Interests::optimizeOrder($oFirst, $sLang, $columns, $this->bAdmin, false);
			return $this->sendResponse($aResult['best']['interests']);
		}
		
		$aResult = [$oFirst->toArray($this->bAdmin)];
		$aPrevious = [$oFirst->id];
		while (!is_null($oInt = Interests::closest($aGeo, false, $sCity, $sLang, $columns, $aPrevious))) {
			$aPrevious[] = $oInt->id;

			if (!$oInt->state) {
				continue;
			}

			$aResult[] = $oInt->toArray($this->bAdmin);
		}

		return $this->sendResponse($aResult);
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

	public function byCityId(Request $oRequest=NULL, $id) {
	    if (is_null($oRequest)) {
	        $oRequest = new Request();
	    }

	    $aGeo = $oRequest->getGeoloc();
	    if (!$aGeo) {
	    	return Parent::byCityId($oRequest, $id);
	    }

	    $skip = $oRequest->getSkip();
	    $limit = $oRequest->getLimit();
	    $sLang = $oRequest->input('lang');
	    $aOrder = $oRequest->input('order');
	    $columns = $oRequest->input('columns');

	    $class = $this->getClass();
	    $oModel = new $class;

	    $where = [[$oModel->table.'.cities_id', '=', $id]];

	    $aResults = [];
	    $aPrevious = [];

	    $i = 0;
	    while (!is_null($oInt = Interests::closest($aGeo, false, $id, $sLang, $columns, $aPrevious))) {
            $oInt->setLang($sLang);
	    	//var_dump($oInt->title);
	    	$oInt->distances = abs($oInt->geoloc->latitude - $aGeo[0]) + abs($oInt->geoloc->longitude - $aGeo[1]);

	    	$aResults[] = $oInt;
	    	$aPrevious[] = $oInt->id;
	    }

	    if ($aOrder && $aOrder[0] === 'distance') {
	    	usort($aResults, function($a, $b) use ($aOrder, $sLang) {
	    		$fact = $aOrder[1] === 'desc' ? -1 : 1;

	    		$a->defineLang($sLang);
	    		$b->defineLang($sLang);
	    		
	    		if ($a->distances == $b->distances) {
	    			return $fact * strcmp($a->title, $b->title);
	    		}

	    		return $fact * (($a->distances < $b->distances) ? -1 : 1);
	    	});
	    }
	    else{
	    	usort($aResults, function($a, $b) use ($aOrder, $sLang) {
	    		$fact = $aOrder[1] === 'desc' ? -1 : 1;

	    		$a->defineLang($sLang);
	    		$b->defineLang($sLang);

	    		$cmp = strcmp($a->title, $b->title);
	    		if (!$cmp) {
	    			if ($a->distance == $b->distance) {
	    				return 0;
	    			}

	    			return $fact * ($a->distance < $b->distance ? -1: 1);
	    		}

	    		return $fact * $cmp;
	    	});
	    }
        
        return $this->sendResponse($aResults, null)->content();
	}
}
