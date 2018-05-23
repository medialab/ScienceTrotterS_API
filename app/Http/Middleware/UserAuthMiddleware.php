<?php

namespace App\Http\Middleware;

use Closure;
use App\Utils\RequestUtil as Request;
use App\Utils\APIControllerUtil as Controller;
use App\Utils\JsonWebTokenUtil;

use App\Models\Users;
use App\Models\UsersToken;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Builder as TokenBuilder;
use Lcobucci\JWT\Parser as TokenParser;
use Lcobucci\JWT\ValidationData;

class UserAuthMiddleware extends Controller
{
    public $sGlobalErrorAccess = 'empty_credentials';

    public function handle(Request $oRequest, Closure $oNext)
    {
		$auth = $oRequest->header("Authorization");
    	if (!$auth) {
            $auth = $oRequest->input("token");
            if (!$auth) {
        		// var_dump("No Auth Header");
        		return response()->json(['error' => 'No Token Specified'], 401);
            }
    	}

    	// var_dump($auth);
    	$token = (new TokenParser())->parse($auth);    	
    	if (!$token) {
    		// var_dump("Bad Token");
    		return response()->json(['error' => 'Bad Token'], 401);
    	}

		$tokenMdl = UsersToken::where('key', $auth)->first();
		if (!$tokenMdl) {
    		// var_dump("Token Not Registerd");
			return response()->json(['error' => 'Token Not Found'], 401);
		}

		$user = Users::where('id', $tokenMdl->user)->first();
		if (!$user) {
    		// var_dump("No User Found");
			return response()->json(['error' => 'User Not Found'], 401);
		}

    	$validationData = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
    	$validationData->setIssuer('http://'.$_SERVER['HTTP_HOST']);
    	$validationData->setAudience('http://'.$_SERVER['HTTP_HOST']);
    	$validationData->setId(UsersToken::idfyUser($user));
    	$validationData->setCurrentTime(time() + 60);

    	//var_dump("Generated ID: ".UsersToken::idfyUser($user));

    	if ($token->isExpired()) {
    		// var_dump("Token Expired");
            $tokenMdl->delete();
            return response()->json(['error' => 'Token Expired'], 440);
        }

        if (!$token->validate($validationData)) {
            // var_dump("Bad Token");
            $tokenMdl->delete();
            return response()->json(['error' => 'Invalid Token'], 401);
        }

		return $oNext($oRequest);
    }
}