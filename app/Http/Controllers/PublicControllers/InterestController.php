<?php

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Interrests;


class InterestsController extends Controller
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
			$oInterests = Interrests::where('state->'.$sLang, 'true')->take($limit)->skip($skip)->get();

			foreach ($oInterests as $key => &$oInt) {
				$oInt->setLang($sLang);
			}
		}
		else{
			$oInterests = Interrests::take($limit)->skip($skip)->get();
		}

		return $this->sendResponse($oInterests->toArray(), null)->content();
	}

	public function get($id) {
		$oCity = Parcours::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null)->content();
	}
}
