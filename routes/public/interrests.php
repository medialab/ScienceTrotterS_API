<?php

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Interrests;


class InterrestsAdminController extends Controller
{
	public function list(Request $oRequest=null)
	{
		if (is_null($oRequest)) {
			return $this->sendResponse([], null)->content();
		}
		
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

$router->get('/list', function() {
	$ctrl = new InterrestsController();
	echo ($ctrl->list());
	exit;
});

$router->get('/{id:[a-z0-9-]+}', function() {
	$ctrl = new InterrestsController();
	$arr = explode('/', $_SERVER['REQUEST_URI']);
	$id = $arr[count($arr)-1];

	echo ($ctrl->get($id));
	exit;
});