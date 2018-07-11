<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Parcours;
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
}
