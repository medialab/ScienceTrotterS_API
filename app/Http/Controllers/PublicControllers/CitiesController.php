<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Cities;

class CitiesController extends Controller
{
	public function list(Request $oRequest)
	{
		$limit = (int)$oRequest->input('limit');
		if (!$limit) {
			$limit = false;
		}
		
		$skip = (int)$oRequest->input('skip');
		if (!$skip) {
			$skip = false;
		}

		$sLang = $oRequest->input('lang');
		if ($sLang) {
			$oCities = Cities::where('state->'.$sLang, 'true')->take($limit)->skip($skip)->get();

			foreach ($oCities as $key => &$oCity) {
				$oCity->setLang($sLang);
			}
		}
		else{
			$oCities = Cities::take($limit)->skip($skip)->get();
		}

		return $this->sendResponse($oCities->toArray(), null)->content();
	}

	public function get($id) {
		$oCity = Cities::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null);
	}
}