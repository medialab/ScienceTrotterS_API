<?php
use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Parcours;

use Illuminate\Http\Request as RequestO;

class ParcoursController extends Controller
{
	public function list()
	{
		$oRequest = Request::capture();
		
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
		global $GET;
		$oCity = Parcours::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null)->content();
	}
}

$router->get('/list', function() {
	$ctrl = new ParcoursController();
	echo ($ctrl->list());
	exit;
});

$router->get('/{id:[a-z0-9-]+}', function() {
	$ctrl = new ParcoursController();
	$arr = explode('/', $_SERVER['REQUEST_URI']);
	$id = $arr[count($arr)-1];

	echo ($ctrl->get($id));
	exit;
});