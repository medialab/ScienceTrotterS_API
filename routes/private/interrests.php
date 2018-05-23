<?php

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Interretsts;


class InterretstsAdminController extends Controller
{
	public function list()
	{
		global $GET;
		$aInterretsts = Interretsts::take((int)$GET['limit'])->skip((int)$GET['offset'])->get();
		return $this->sendResponse($aInterretsts->toArray(), null)->content();
	}

	public function get($id) {
		global $GET;
		$oCity = Interretsts::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null)->content();
	}
}

$router->get('/list', function() {
	$ctrl = new InterretstsAdminController();
	echo ($ctrl->list());
	exit;
});

$router->get('/{id:[a-z0-9-]+}', function() {
	$ctrl = new InterretstsAdminController();
	$arr = explode('/', $_SERVER['REQUEST_URI']);
	$id = $arr[count($arr)-1];

	echo ($ctrl->get($id));
	exit;
});