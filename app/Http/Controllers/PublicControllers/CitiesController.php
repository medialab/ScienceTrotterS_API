<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Cities;

class CitiesController extends Controller
{
	public function sendResponse($data, $msg) {
		if (!empty($_POST['callback'])) {
			$cl = $_POST['callback'];
		}
		elseif (!empty($_GET['callback'])) {
			$cl = $_GET['callback'];
		}
		else{
			$cl = false;
		}

		if ($cl) {
			echo $cl.'('.json_encode([
				'success' => true,
				'data' => $data,
				'message' => $msg
			]).')';
			exit;	
		}

		return Parent::sendResponse($data, $msg);
	}

	public function list(Request $oRequest)
	{
		$aCities = Cities::where('state', true)->take($oRequest->input('limit'))->skip($oRequest->input('offset'))->get();
		return $this->sendResponse($aCities->toArray(), null);
	}

	public function get($id) {
		$oCity = Cities::where('id', $id)->first();
		return $this->sendResponse($oCity->toArray(), null);
	}
}
