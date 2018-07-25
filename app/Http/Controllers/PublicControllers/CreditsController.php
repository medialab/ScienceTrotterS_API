<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Credits;

class CreditsController extends Controller
{
	protected $bAdmin = false;
	protected $sModelClass = 'Credits';

	public function latest(Request $oRequest) {
		$oModel = Parent::latest($oRequest);
		$oModel = $oModel->get()->first();

		if (is_null($oModel)) {
			return $this->sendResponse(false);
		}

		if (file_exists(PUBLIC_PATH.$oModel->css)) {
			$oModel->css = file_get_contents(PUBLIC_PATH.$oModel->css);
		}

		return $this->sendResponse($oModel->toArray());
	}
}