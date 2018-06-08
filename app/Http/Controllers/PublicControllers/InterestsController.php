<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Models\Interests;
use Database\Mockup\DefaultMockup;
use App\Utils\CheckerUtil;

class InterestsController extends Controller
{
  protected $bAdmin = false;
  protected $sModelClass = 'Interests';
  /*public function list() {
    $aData = Interests::where('state', '=', 'true')
      ->get()
      ->toArray();
    return $this->sendResponse($aData);
  }*/

/*  public function byParcourId($id, Request $oRequest) {
    $aData = [];

    if (CheckerUtil::is_uuid_v4($id)) {
      $aWhereClauses = [
        ['state', '=', 'true'],
        ['parcours_id', '=', $id]
      ];
      $aData = Interests::where($aWhereClauses)
        ->get()
        ->toArray();
    }

    return $this->sendResponse($aData);
  }*/

  public function byCityId($sCityId) {
    $aData = [];

    if (CheckerUtil::is_uuid_v4($sCityId)) {
      $aWhereClauses = [
        ['state', '=', 'true'],
        ['cities_id', '=', $sCityId]
      ];
      $aData = Interests::where($aWhereClauses)
        ->get()
        ->toArray();
    }

    return $this->sendResponse($aData);
  }

  public function byId($sInterestId) {
    $aData = [];

    if (CheckerUtil::is_uuid_v4($sInterestId)) {
      $aWhereClauses = [
        ['state', '=', 'true'],
        ['id', '=', $sInterestId]
      ];
      $aData = Interests::where($aWhereClauses)
        ->get()
        ->toArray();
    }

    return $this->sendResponse($aData);
  }

}
