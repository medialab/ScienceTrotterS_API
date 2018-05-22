<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Cities;

class CitiesAdminController extends CitiesController
{
	public function update(Request $oRequest) {
		var_dump('label', $oRequest->input('label'));
		var_dump('geoloc', $oRequest->input('geoloc'));
		exit;
	}
}
