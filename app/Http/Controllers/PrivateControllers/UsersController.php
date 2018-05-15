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

		var_dump("Getting User");
		$user = Users::where('email', $request->input('email'))->first();
		
	    if($request->input('password') === $user->password){
			var_dump("Getting Token");
			$token = false;
	    	$tokenMdl = UsersToken::where('user', $user->id)->first();

	    	if (!empty($tokenMdl)) {
	    		$token = (new TokenParser())->parse($tokenMdl->key);
	    	}

    		var_dump("Generating New Token");
			$tokenMdl = UsersToken::generateToken($user);
	    	/*if (!$token || $token->isExpired()) {
	    	}
	    	else{
	    		var_dump("Token Exists");
	    		var_dump($tokenMdl->id,$tokenMdl->user,$tokenMdl->key);

	    		$token->setExpiration(time() + UsersToken::getExpireDelay());
	    		$tokenMdl->key = (string)$token;
	    		$tokenMdl->update();
	    		var_dump("Token Updated");
	    	}*/

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
