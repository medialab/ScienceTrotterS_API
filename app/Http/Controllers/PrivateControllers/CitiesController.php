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
		$aCities = Cities::all();
		return $this->sendResponse($oUsers->toArray(), null);
	}

	public function get(Request $oRequest) {
		$this->validate($request, [
			'id' => 'required',
		]);

		$oCity = Cities::where('id', $oRequest->input('id'))->first();
		return $this->sendResponse($oCity->toArray(), null);
	}

	public function find($id) {
		$oCity = Cities::where('id', 'ca3e834d-c717-4832-ab8b-c50ebd1bd3d6')->first();
		return $this->sendResponse($oCity->toArray(), null);
	}
}
