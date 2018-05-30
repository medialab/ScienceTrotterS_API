<?php 

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Interests;

class InterestsAdminController extends InterestsController
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
			$oInterests = Interests::take($limit)->skip($skip)->get();

			foreach ($oInterests as $key => &$oInt) {
				$oInt->setLang($sLang);
			}
		}
		else{
			$oInterests = Interests::take($limit)->skip($skip)->get();
		}

		return $this->sendResponse($oInterests->toArray(), null)->content();
	}

	public function get($id) {
		$oCity = Parcours::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null)->content();
	}
}
