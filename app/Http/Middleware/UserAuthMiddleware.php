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

    /**
     * Force La Connexion Avant D'executer La Requête
     * @param  Request $oRequest La Requete
     * @param  Closure $oNext    Paramètre Lumen
     * @return Json            Réponse De la Requete
     */
    public function handle(Request $oRequest, Closure $oNext)
    {
        // Récupération Du Token
        $auth = $oRequest->header("Authorization");
        if (!$auth) {
            $auth = $oRequest->input("token");
            if (!$auth) {
                return response()->json(['error' => 'No Token Specified'], 401);
            }
        }

        // Vérification Du Token
        $token = (new TokenParser())->parse($auth);     
        if (!$token) {
            return response()->json(['error' => 'Bad Token'], 401);
        }

        // Vérification De L'existance du Token
        $tokenMdl = UsersToken::where('key', $auth)->first();
        if (!$tokenMdl) {
            return response()->json(['error' => 'Token Not Found'], 401);
        }

        // Récupération Du User
        $user = Users::where('id', $tokenMdl->user)->first();
        if (!$user) {
            return response()->json(['error' => 'User Not Found'], 401);
        }

        // validation Des Donnés du Token
        $validationData = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $validationData->setIssuer('http://'.$_SERVER['HTTP_HOST']);
        $validationData->setAudience('http://'.$_SERVER['HTTP_HOST']);
        $validationData->setId(UsersToken::idfyUser($user));
        $validationData->setCurrentTime(time() + 60);


        // Si le Token a expiré
        if ($token->isExpired()) {
            $tokenMdl->delete();
            return response()->json(['error' => 'Token Expired'], 440);
        }

        // Si la Validation a échoué
        if (!$token->validate($validationData)) {
            $tokenMdl->delete();
            return response()->json(['error' => 'Invalid Token'], 401);
        }

        // Execution de la Requête
		return $oNext($oRequest);
    }
}