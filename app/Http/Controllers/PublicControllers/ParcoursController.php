<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Parcours;
use App\Utils\CheckerUtil;

class ParcoursController extends Controller
{
  protected $sModelClass = 'Parcours';
  /*public function list() {
    $aData = Parcours::where('state', '=', 'true')
      ->get()
      ->toArray();
    return $this->sendResponse($aData);
  }*/

  public function byCityId($sCityId) {
    $aData = [];

    if (CheckerUtil::is_uuid_v4($sCityId)) {
      $aWhereClauses = [
        ['state', '=', 'true'],
        ['cities_id', '=', $sCityId]
      ];
      $aData = Parcours::where($aWhereClauses)
        ->get()
        ->toArray();
    }

    return $this->sendResponse($aData);
  }

  public function byId($sParcourId) {
    $aData = [];

    if (CheckerUtil::is_uuid_v4($sParcourId)) {
      $aWhereClauses = [
        ['state', '=', 'true'],
        ['id', '=', $sParcourId]
      ];
      $aData = Parcours::where($aWhereClauses)
        ->get()
        ->toArray();
    }

    return $this->sendResponse($aData);
  }

}
