<?php

namespace App\Http\Middleware;

use Closure;
use App\Utils\RequestUtil as Request;
use App\Utils\APIControllerUtil as Controller;
use App\Utils\JsonWebTokenUtil;
use App\Models\UserTokenModel;

class UserAuthMiddleware extends Controller
{
    public $sGlobalErrorAccess = 'empty_credentials';

    public function handle(Request $oRequest, Closure $oNext)
    {
      var_dump("TEST AUTH");
      exit;
    }
}