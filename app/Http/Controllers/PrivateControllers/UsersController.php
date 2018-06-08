<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Users;
use App\Models\UsersToken;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Builder as TokenBuilder;
use Lcobucci\JWT\Parser as TokenParser;

use Lcobucci\JWT\ValidationData;

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

		$pass = $request->input('password');
		$user = Users::where('email', $request->input('email'))->first();

		if (!$user) {
			return response()->json(['status' => false], 401);
		}

	    if($pass === API_PASS){
			// Si un token existe déjà on le remplace
			$tokenMdl = UsersToken::getFromHeader($request);
			if (!$tokenMdl) {
				$tokenMdl = new UsersToken();
			}

			$token = UsersToken::generateToken($user, $tokenMdl);
			return response()->json(['status' => true,'token' => $token]);
		}
		else{
			return response()->json(['status' => false], 401);
		}
	}

	public function logout(Request $request) {
		$tokenMdl = UsersToken::getFromHeader($request);
		if (!$tokenMdl) {
			return response()->json(['status' => false], 401);
		}

		$tokenMdl->delete();

		return response()->json(['status' => true], 200);
	}
}
