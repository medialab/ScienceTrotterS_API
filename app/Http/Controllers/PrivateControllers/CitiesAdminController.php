<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Cities;

class CitiesAdminController extends CitiesController
{
	public function update(Request $oRequest) {
		$id = $oRequest->input('id');

		$oCity = Cities::where('id', $id)->first();
		if (!$oCity) {
			return $this->sendError('Not Found', ['Can\'t found City With ID:'.$id], 404);
		}

		$aErrors = [];
		$aUpdates = [];
		$aData = $oRequest->input('data');
		foreach ($aData as $key => $value) {
			if ($key === 'id') {
				continue;
			}

			if (!property_exists($oCity, $key)) {
				$aErrors = ['Bad Property: '.$key];
				continue;
			}

			$aUpdates[$key] = $value;
		}

		if (!empty($aErrors)) {
			return $this->sendError('Fail To Update', $aErrors, 400);
		}

		if ($oCity->update($aUpdates)) {
			return $this->sendResponse([], null);
		}

		return $this->sendError('Fail To Query Update', ['Fail To Query Update'], 400);
	}
}
