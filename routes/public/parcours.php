<?php
use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Parcours;


class ParcoursController extends Controller
{
	public function list()
	{
		//$aParcours = Parcours::where('state', true)->take((int)$GET['limit'])->skip((int)$GET['offset'])->get();
		
		//$aParcours = Parcours::take(10)->get();
		$oParcours = Parcours::take(10)->get();

		$sLang = empty($_GET['lang']) ? false : $_GET['lang'];
		var_dump($_GET);

		if ($sLang) {
			foreach ($oParcours as $key => &$oParc) {
				$oParc->setLang($sLang);
				var_dump($oParc->title);
			}
		}
		/*var_dump($oParcours->title);
		var_dump($oParcours->setLang('fr'));
		var_dump($oParcours->title);
		var_dump($oParcours->setLang('en'));
		var_dump($oParcours->title);
		var_dump($oParcours->setLang());*/
		var_dump($oParcours->toArray());
		exit;

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