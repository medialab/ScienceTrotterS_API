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

	/**
	 * Écrit Dans les Logs du Web Server actif (Apache/ngix)
	 */
	private function logError($sMsg) {
	    $sDate = date('Y-m-d H:i:d');
		$sAddress = $_SERVER['REMOTE_ADDR'];

	    $sMsg = "
	        ============== API: {$sDate} ==============
	            Type: Fail To Login To Api

	            ++++ Api Error:
	                -- method: {$_SERVER['REQUEST_METHOD']}
	                -- Url: {$_SERVER['REQUEST_URI']}
	                -- User IP: {$sAddress}
	                -- Message: {$sMsg}
	    ";

	    $sMsg = preg_replace("/\t{3}/", "", $sMsg);
	    error_log($sMsg);
	}

	/**
	 * Connexion Utilisateur
	 * @param  Request $request La Requete
	 * @return Json           Résultat
	 */
	public function login(Request $request) {
		$this->validate($request, [
			'email' => 'required',
			'password' => 'required'
		]);

		// Récupération Du User Demandé
		$pass = $request->input('password');
		$user = Users::where('email', $request->input('email'))->first();

		// Si User Introuvable
		if (!$user) {
			$this->logError('Bad Password');
			return response()->json(['success' => false, 'message' => 'Identifiant / Mot de passe invalides.'], 401);
		}
		
		// Vérification Du PassWord
		if($pass === API_PASS){

			// Si un token existe déjà on le remplace
			$tokenMdl = UsersToken::getFromHeader($request);
			if (!$tokenMdl) {
				$tokenMdl = new UsersToken();
			}

			// Génération Du Token
			$token = UsersToken::generateToken($user, $tokenMdl);
			if (!$token) {
				$this->logError('Fail To Générate Token');
				return response()->json(['success' => false, 'message' => 'Impossible de générer un nouveau Token', 'code' => 500], 400);
			}

			return response()->json(['success' => true,'token' => $token]);
		}
		else{
			$this->logError('Bad Password');
			return response()->json(['success' => false, 'message' => 'Identifiant / Mot de passe invalides.'], 401);
		}
	}

	/**
	 * Déconnexion
	 * @param  Request $request La Requete
	 * @return Json           Succèss
	 */
	public function logout(Request $request) {
		// Récupération Du Token
		$tokenMdl = UsersToken::getFromHeader($request);

		// Si Le Token Est Introuvable
		if (!$tokenMdl) {
			return response()->json(['success' => false], 401);
		}

		// On Supprime Le Token
		$tokenMdl->delete();
		return response()->json(['success' => true], 200);
	}
}
