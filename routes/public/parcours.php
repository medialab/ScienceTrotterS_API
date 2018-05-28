<?php
use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Parcours;


class ParcoursController extends Controller
{
	public function list()
	{
		global $GET;
		//$aParcours = Parcours::where('state', true)->take((int)$GET['limit'])->skip((int)$GET['offset'])->get();
		
		//$aParcours = Parcours::take(10)->get();
		$oParcours = Parcours::take(10)->get();

		$sLang = empty($GET['lang']) ? false : $GET['lang'];

		foreach ($oParcours as $key => &$oParc) {
			var_dump($key);
			var_dump($oParc);
		}
		/*var_dump($oParcours->title);
		var_dump($oParcours->setLang('fr'));
		var_dump($oParcours->title);
		var_dump($oParcours->setLang('en'));
		var_dump($oParcours->title);
		var_dump($oParcours->setLang());*/
		exit;

		return $this->sendResponse($aParcours->toArray(), null)->content();
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