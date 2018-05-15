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
		$auth = $oRequest->header("Authorization");
    	$token = (new TokenParser())->parse($auth);

    	if (!$token) {
    		return false;
    	}

		$tokenMdl = UsersToken::where('key', $auth)->first();
		if (!$tokenMdl) {
			return false;
		}

		var_dump("AUTH Header: ".$auth);
		$user = Users::where('id', $tokenMdl->user)->first();
		UsersToken::generateToken($user, $tokenMdl);

		return true;
    }
}