<?php

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Interrests;


class InterrestsAdminController extends Controller
{
	public function list()
	{
		global $GET;
		$aInterrests = Interrests::take((int)$GET['limit'])->skip((int)$GET['offset'])->get();
		return $this->sendResponse($aInterrests->toArray(), null)->content();
	}

	public function get($id) {
		global $GET;
		$oCity = Interrests::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null)->content();
	}
}

$router->get('/list', function() {
	$ctrl = new InterrestsAdminController();
	echo ($ctrl->list());
	exit;
});

$router->get('/{id:[a-z0-9-]+}', function() {
	$ctrl = new InterrestsAdminController();
	$arr = explode('/', $_SERVER['REQUEST_URI']);
	$id = $arr[count($arr)-1];

	echo ($ctrl->get($id));
	exit;
});