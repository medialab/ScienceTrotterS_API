<?php
use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;

class ParcoursController extends Controller
{
	public function list()
	{
		$aParcours = Parcours::where('state', true)->take((int)$_GET['limit'])->skip((int)$_GET['offset'])->get();
		return $this->sendResponse($aParcours->toArray(), null);
	}

	public function get($id) {
		$oCity = Parcours::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null);
	}
}


$router->get('/list', function() {
	$ctrl = new ParcoursController();
	echo $ctrl->list();
	exit;
});

$router->get('/{id:[a-z0-9-]+}', function() {
	$ctrl = new ParcoursController();
	$arr = explode('/', $_SERVER['REQUEST_URI']);
	$id = $arr[count($arr)-1];

	echo $ctrl->get($id);
	exit;
});