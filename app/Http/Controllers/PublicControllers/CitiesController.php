<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Cities;

class CitiesController extends Controller
{

    public function list() {
      $aCities = Cities::where('state', 1)
        ->get()
        ->makeHidden('state')
        ->makeHidden('created_at')
        ->makeHidden('updated_at')
        ->toArray();

      return $this->sendResponse($aCities, null);
    }

}
