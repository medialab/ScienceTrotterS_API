<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Cities;
use App\Utils\CheckerUtil;

class CitiesController extends Controller
{
  protected $sModelClass = 'Cities';

  public function byId($sCityId) {
    $aData = [];

    if (CheckerUtil::is_uuid_v4($sCityId)) {
      $aWhereClauses = [
        ['state', '=', 'true'],
        ['id', '=', $sCityId]
      ];
      $aData = Cities::where($aWhereClauses)
        ->get()
        ->toArray();
    }

    return $this->sendResponse($aData);
  }

}