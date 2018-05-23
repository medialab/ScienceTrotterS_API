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
			if (in_array($key, ['id', 'created_at', 'updated_at'])) {
				continue;
			}
/*
			if (!property_exists($oCity, $key)) {
				$aErrors = ['Bad Property: '.$key];
				continue;
			}*/

			$aUpdates[$key] = $value;
		}
/*
		if (!empty($aErrors)) {
			var_dump(get_class_vars($oCity));
			return $this->sendError('Fail To Update', $aErrors, 400);
		}
*/
		if ($oCity->update($aUpdates)) {
			return $this->sendResponse([], null);
		}

		return $this->sendError('Fail To Query Update', ['Fail To Query Update'], 400);
	}

	public function insert(Request $oRequest) {
		$aData = $oRequest->input('data');

		if (empty($aData['label'])) {
			return $this->sendError('Error: Missing City Label', ['Error: Missing City Label'], 400);
		}

		$oCity = new Cities;
		$oCity->label = $aData['label'];
		$oCity->save();

		if ($oCity->update($aData)) {
			return $this->sendResponse(['id' => $oCity->id], null);
		}

		return $this->sendError('Fail To Query Insert', ['Fail To Query Insert'], 400);
	}
}
