<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Parcours;

class ParcoursController extends Controller
{
	public function list(Request $oRequest)
	{
		$limit = (int)$oRequest->input('limit');
		if (!$limit) {
			$limit = 15;
		}
		
		$skip = (int)$oRequest->input('skip');
		if (!$skip) {
			$skip = false;
		}

		$sLang = $oRequest->input('lang');
		if ($sLang) {
			$oParcours = Parcours::where('state->'.$sLang, 'true')->take($limit)->skip($skip)->get();

			foreach ($oParcours as $key => &$oParc) {
				$oParc->setLang($sLang);
			}
		}
		else{
			$oParcours = Parcours::take($limit)->skip($skip)->get();
		}

		return $this->sendResponse($oParcours->toArray(), null)->content();
	}

	public function get($id) {
		$oCity = Parcours::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null)->content();
	}
}
