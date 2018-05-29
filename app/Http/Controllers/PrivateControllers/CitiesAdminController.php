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
		$limit = (int)$oRequest->input('limit');
		if (!$limit) {
			$limit = false;
		}
		
		$skip = (int)$oRequest->input('skip');
		if (!$skip) {
			$skip = false;
		}

		$oCities = Cities::take($limit)->skip($skip)->get();

		$sLang = $oRequest->input('lang');
		if ($sLang) {
			foreach ($oCities as $key => &$oCity) {
				$oCity->setLang($sLang);
			}
		}

		return $this->sendResponse($oCities->toArray(), null)->content();
	}
	
	public function update(Request $oRequest) {
		$id = $oRequest->input('id');

		$oCity = Cities::where('id', $id)->first();
		if (!$oCity) {
			return $this->sendError('Not Found', ['Can\'t found City With ID:'.$id], 404);
		}

		$sLang = $aData = $oRequest->input('lang');
		$oCity->setLang($sLang);

		$aErrors = [];
		$aUpdates = [];
		$aData = $oRequest->input('data');

		var_dump("UPDATING CITY", $aData);

		foreach ($aData as $key => $value) {
			/* Données à Ignorer lors de l'update */
			if (in_array($key, ['id', 'created_at', 'updated_at'])) {
				continue;
			}
			elseif ($key === 'image' && empty($value)) {
				continue;
			}

			var_dump("UPDATING CITY", $aData);
			$oCity->$key = $value;
		}
		
		if(!empty($aUpdates['image']) && $aUpdates['image'] !== $oCity->image) {
			$this->downloadImage($aUpdates['image']);
		}

		/* La ville ne peut être activée que si tout les champs sont remplis */
		if (!strlen($oCity->image) || !strlen($oCity->geoloc)) {
			$oCity->state = false;
		}

		if ($oCity->save()) {
			return $this->sendResponse($oCity, null);
		}

		return $this->sendError('Fail To Query Update', ['Fail To Query Update'], 400);
	}

	public function insert(Request $oRequest) {
		$aData = $oRequest->input('data');

		if (empty($aData['label'])) {
			return $this->sendError('Error: Missing City Label', ['Error: Missing City Label'], 400);
		}

		$sLang = $oRequest->input('lang');
		if (!$sLang) {
			return $this->sendError('Fail To Query Insert', ['Lang Param is Requested'], 400);
		}

		$oCity = new Cities;
		$oCity->setLang($sLang);
		$oCity->state = false;
		$oCity->label = $aData['label'];

		$oCity->geoloc = $aData['geoloc'];

		if (!empty($aData['image'])) {
			$this->downloadImage($aData['image']);
			$oCity->image = $aData['image'];
		}

		/* La ville ne peut être activée que si tout les champs sont remplis */
		if (!strlen($oCity->image) || !strlen($oCity->geoloc)) {
			$oCity->state = false;
		}

		if ($oCity->save()) {
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
