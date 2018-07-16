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
    const NO_TOKEN = [
        'code' => 0, 
        'msg' => 'No Token Specified',
        'usrMsg' => 'Aucun Token spécifié.',
    ];

    const BAD_TOKEN = [
        'code' => 1, 
        'msg' => 'Bad Token',
        'usrMsg' => 'Le format du Token est invalide.',
    ];
    const NOT_FOUND = [
        'code' => 2, 
        'msg' => 'Token Not Found',
        'usrMsg' => 'Le token est introuvable',
    ];
    const NO_USER = [
        'code' => 3, 
        'msg' => 'User Not Found',
        'usrMsg' => 'L\'utilisateur est introuvable',
    ];
    const EXPIRED = [
        'code' => 4, 
        'msg' => 'Token Expired',
        'usrMsg' => 'La session a expiré',
    ];
    const INVALID = [
        'code' => 5, 
        'msg' => 'Invalid Token',
        'usrMsg' => 'Le Token est invalide',
    ];

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
                return response()->json(Self::NO_TOKEN, 401);
            }
        }

        try {
            //throw new \Exception("Error Processing Request", 1);
            
            // Vérification Du Token
            $token = (new TokenParser())->parse($auth);     
            if (!$token) {
                return response()->json(Self::NO_TOKEN, 401);
            }

            // Vérification De L'existance du Token
            $tokenMdl = UsersToken::where('key', $auth)->first();
            if (!$tokenMdl) {
                return response()->json(Self::NOT_FOUND, 401);
            }

            // Récupération Du User
            $user = Users::where('id', $tokenMdl->user)->first();
            if (!$user) {
                return response()->json(Self::NO_USER, 401);
            }

            // Si le Token a expiré
            if ($token->isExpired()) {
                $tokenMdl->delete();
                return response()->json(Self::EXPIRED, 440);
            }

            // Si la Validation a échoué
            if (!UsersToken::validateToken($user, $token)) {
                $tokenMdl->delete();
                return response()->json(Self::INVALID, 401);
            }

            // Execution de la Requête
    		return $oNext($oRequest);
            
        } catch (\Exception $e) {
            return response()->json(SELF::BAD_TOKEN, 401);
        }
    }
}