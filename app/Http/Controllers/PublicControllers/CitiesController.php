<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Cities;

class CitiesController extends Controller
{
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