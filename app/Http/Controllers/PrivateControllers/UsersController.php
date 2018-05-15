<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Users;
use App\Models\UsersToken;

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

	public function login(Request $request) {
		$this->validate($request, [
			'email' => 'required',
			'password' => 'required'
		]);

		$user = Users::where('email', $request->input('email'))->first();
		 
	    if(Hash::check($request->input('password'), $user->password)){
			$token = new UsersToken($this);
			exit;

			Users::where('email', $request->input('email'))->update(['token' => $token]);;

			return response()->json(['status' => 'success','token' => $token]);
		}
		else{
			return response()->json(['status' => 'fail'],401);
		}
	}

	public function logout(Request $request) {
		var_dump("LOGIN OUT");
		exit;
	}
}
