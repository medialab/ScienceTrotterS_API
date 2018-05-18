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
		$aParcours = Parcours::where('state', true)->take((int)$GET['limit'])->skip((int)$GET['offset'])->get();
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
	echo var_dump($ctrl->list());
	exit;
});

$router->get('/{id:[a-z0-9-]+}', function() {
	$ctrl = new ParcoursController();
	$arr = explode('/', $_SERVER['REQUEST_URI']);
	$id = $arr[count($arr)-1];

	echo var_dump($ctrl->get($id));
	exit;
});