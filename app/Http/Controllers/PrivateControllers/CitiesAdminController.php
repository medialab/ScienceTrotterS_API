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
			elseif ($key === 'image' && empty($value)) {
				continue;
			}
			$aUpdates[$key] = $value;
		}

		if (empty($aUpdates['image']) || empty($aUpdates['geoloc'])) {
			$aUpdates['state'] = false;
		}
		elseif(!empty($aUpdates['image'])) {
			$dir = dirname(UPLOAD_PATH.$aUpdates['image']);
			if (!is_dir($dir)) {
				var_dump("CREATE DIR: ". $dir);
				mkdir($dir, 0775, true);
			}

			var_dump(UPLOAD_PATH.$aUpdates['image']);
			var_dump(ADMIN_URL.'upload/'.$aUpdates['image']);

			$sPath = UPLOAD_PATH.$aUpdates['image'];
			if (file_exists($sPath)) {
				unlink($sPath);
			}
			
			file_put_contents($sPath, fopen(ADMIN_URL.'upload/'.$aUpdates['image'], 'r'));
		}

		if ($oCity->update($aUpdates)) {
			return $this->sendResponse(['data' => $oCity], null);
		}

		return $this->sendError('Fail To Query Update', ['Fail To Query Update'], 400);
	}

	public function insert(Request $oRequest) {
		$aData = $oRequest->input('data');

		if (empty($aData['label'])) {
			return $this->sendError('Error: Missing City Label', ['Error: Missing City Label'], 400);
		}

		$oCity = new Cities;
		$oCity->state = false;
		$oCity->label = $aData['label'];
		$oCity->save();

		if (empty($aData['image']) || empty($aData['geoloc'])) {
			$aData['state'] = false;
		}

		if ($oCity->update($aData)) {
			return $this->sendResponse(['data' => $oCity], null);
		}

		return $this->sendError('Fail To Query Insert', ['Fail To Query Insert'], 400);
	}
}
