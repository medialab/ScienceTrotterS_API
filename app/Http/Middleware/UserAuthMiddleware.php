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
    	var_dump("Verifying");
		$auth = $oRequest->header("Authorization");
    	if (!$auth) {
    		var_dump("No Auth Header");
    		return response()->json(['error' => 'Unauthorized'], 401);
    	}

    	var_dump($auth);
    	$token = (new TokenParser())->parse($auth);

    	$validationData = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
    	$validationData->setIssuer('http://'.$_SERVER['HTTP_HOST']);
    	$validationData->setAudience('http://'.$_SERVER['HTTP_HOST']);
    	$validationData->setId(UsersToken::idfy($user));

    	if (!$token || !$token->validate($validationData)) {
    		var_dump("Bad Token");
    		return response()->json(['error' => 'Invalid Token'], 401);
    	}

    	if ($token->isExpired()) {
    		var_dump("Token Expired");
    		return response()->json(['error' => 'Token Expired'], 401);
    	}

		$tokenMdl = UsersToken::where('user', $token->getClaim('uid'))->first();
		if (!$tokenMdl) {
    		var_dump("Token Not Registerd");
			return response()->json(['error' => 'Unauthorized'], 401);
		}

		var_dump("AUTH Header: ".$auth);
		$user = Users::where('id', $tokenMdl->user)->first();
		UsersToken::generateToken($user, $tokenMdl);

		return $oNext($oRequest);
    }
}