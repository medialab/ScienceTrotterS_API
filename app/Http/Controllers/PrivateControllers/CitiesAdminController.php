<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Cities;

class CitiesAdminController extends CitiesController
{
	/**
	 * Télécharge l'image de la ville depuis le serveur d'Admin
	 * @param  String $sName Nom de l'image
	 */
	private function downloadImage($sName) {
		/* On crée le dossier de l'image */
		$dir = dirname(UPLOAD_PATH.$sName);
		if (!is_dir($dir)) {
			//var_dump("CREATE DIR: ". $dir);
			mkdir($dir, 0775, true);
		}

		/* Url De téléchargement de l'image */
		$imgUrl = ADMIN_URL.'upload/'.$sName;

		/* si l'image existe on la remplace */
		$sPath = UPLOAD_PATH.$sName;
		if (file_exists($sPath)) {
			unlink($sPath);
		}

		file_put_contents($sPath, fopen($imgUrl, 'r'));
	}

	public function list(Request $oRequest) {
		$aCities = Cities::take($oRequest->input('limit'))->skip($oRequest->input('offset'))->get();
		return $this->sendResponse($aCities->toArray(), null);
	}
	
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
			/* Données à Ignorer lors de l'update */
			if (in_array($key, ['id', 'created_at', 'updated_at'])) {
				continue;
			}
			elseif ($key === 'image' && empty($value)) {
				continue;
			}

			$aUpdates[$key] = $value;
		}

		/* La ville ne peut être activée que si tout les champs sont remplis */
		if (empty($aUpdates['geoloc']) || (empty($aUpdates['image']) && empty($oCity->image))) {
			$aUpdates['state'] = false;
		}
		elseif(!empty($aUpdates['image']) && $aUpdates['image'] !== $oCity->image) {
			$this->downloadImage($aUpdates['image']);
		}

		if ($oCity->update($aUpdates)) {
			return $this->sendResponse($oCity, null);
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

		/* La ville ne peut être activée que si tout les champs sont remplis */
		if (empty($aData['image']) || empty($aData['geoloc'])) {
			$aData['state'] = false;
		}
		elseif (!empty($aData['image'])) {
			$this->downloadImage($aData['image']);
		}

		if ($oCity->update($aData)) {
			return $this->sendResponse($oCity, null);
		}

		return $this->sendError('Fail To Query Insert', ['Fail To Query Insert'], 400);
	}

	public function delete(Request $oRequest) {
		$this->validate($oRequest, [
			'id' => 'required',
		]);

		$id = $oRequest->input('id');

		$oCity = Cities::where('id', $id);

		if ($oCity->delete()) {
			return $this->sendResponse(true, null);
		}

		return $this->sendError('Fail To Query Delete', ['Fail To Query Delete'], 400);
	}
}
