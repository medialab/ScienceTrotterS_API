<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Users;

class UsersController extends Controller
{

      public function home(Request $oRequest)
      {
          $aPatterns = [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|between:6,20'
          ];
          $bValidator = Validator::validateOrError($this, $oRequest->all(), $aPatterns);

          $oUser = new Users();
          $oUser->firstname = $oRequest->firstname;
          $oUser->lastname = $oRequest->lastname;
          $oUser->email = $oRequest->email;
          $oUser->password = $oRequest->password;
          $oUser->state = 1;
          $oUser->save();

          return $this->sendResponse([], 'Users retrieved successfully.');
      }

      public function list(Request $oRequest)
      {
        $oUsers = Users::all();
        return $this->sendResponse($oUsers->toArray(), null);
      }

}
