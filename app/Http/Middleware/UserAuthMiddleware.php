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

    	if (!$token || $token->isExpired()) {
    		var_dump("Bad Token");
    		return response()->json(['error' => 'Unauthorized'], 401);
    	}

		$tokenMdl = UsersToken::where('key', $auth)->first();
		
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