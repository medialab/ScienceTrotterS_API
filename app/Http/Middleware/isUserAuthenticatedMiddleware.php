<?php

namespace App\Http\Middleware;

use Closure;
use App\Utils\RequestUtil as Request;
use App\Utils\APIControllerUtil as Controller;
use App\Utils\JsonWebTokenUtil;
use App\Models\UserTokenModel;

class isUserAuthenticatedMiddleware extends Controller
{
    public $sGlobalErrorAccess = 'empty_credentials';

    public function handle(Request $oRequest, Closure $oNext)
    {
      if (empty($oRequest->bearerToken())) {
        return $this->sendError($this->sGlobalErrorAccess);
      }

      return $oNext($oRequest);
    }
}