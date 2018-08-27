<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser as TokenParser;
use Lcobucci\JWT\Builder as TokenBuilder;

class UsersToken extends Model
{
	// Durée de Vie D'un Token
	private static $expireDelay = API_SESSION_LIFETIME;

	protected $table = 'users_token';

	/**
	 * Types des colones particulières 
	 */
	protected $casts = [
			'id' => 'string',
	];

	/**
	 * Varialbles modifialbes en DB
	 */
	protected $fillable = ['user','key'];

	/**
	 * Variable à ne pas récupérer
	 */
	protected $hidden = [
		'id'
	];

	function __construct(array $attributes = [], Users $user=null) {
		Parent::__construct($attributes);

		if (!is_null($user)) {
			$this->user = $user->id;
		}
	}

	/**
	 * Récupère La Durée de Vie D'un Token
	 */
	public static function getExpireDelay() {
		return Self::$expireDelay;
	}

	/**
	 * Crée un Hash à partir d'un Utilisateur
	 * @param  Users  $user L'utilisateur
	 * @return String       Le Hash
	 */
	public static function idfyUser(Users $user) {
		return md5($user->id.'-'.$user->created_at.'-'.$user->email);
	}
	
	public static function validateToken(Users $user, Token $token, $bextend=false) {
		// validation Des Donnés du Token
		$validationData = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
		$validationData->setIssuer('http://'.$_SERVER['HTTP_HOST']);
		$validationData->setAudience('http://'.$_SERVER['HTTP_HOST']);
		$validationData->setId(Self::idfyUser($user));
		$validationData->setCurrentTime(time());

		$b = $token->validate($validationData);

		if (!$b || !$bextend) {
			return $b;
		}

		$old = (string) $token;

		$tokenMdl = UsersToken::where('key', $old)->get()->first();

		if (!$tokenMdl) {
			return false;
		}


		if (!Self::validateToken($user, $token)) {
			return false;
		}

		$token = Self::gen($user);
		$tokenMdl->key = (string) $token;
		$tokenMdl->save();
		
		return $token;
	}

	private static function gen(Users $user) {
		return (new TokenBuilder())
			// Définition Domaine
			->setIssuer('http://'.$_SERVER['HTTP_HOST'])
			->setAudience('http://'.$_SERVER['HTTP_HOST'])
			// Définition Hash User
			->setId(Self::idfyUser($user), true)
			// Définition Id User
			->set('uid', $user->id)
			// Définition de l'heure de création
			->setIssuedAt(time())
			// Définition de l'heure de départ
			->setNotBefore(time())
			// Définition de l'heure d'Expiration'
			->setExpiration(time() + Self::$expireDelay)
			// Génération du Token
			->getToken()
		;
	}

	/**
	 * Crée Un Nouveau Token Pour un Utilisateur
	 * @param  Users           $user     L'Utilisateur
	 * @param  UsersToken $tokenMdl Token à Mettre à jour Ou NULL pour une Création
	 * @return String                    Le Token
	 */
	public static function generateToken(Users $user, UsersToken $tokenMdl=null) {
		if (is_null($tokenMdl)) {
			$tokenMdl = new UsersToken([], $user);
		}

		$token = (new TokenBuilder())
			// Définition Domaine
			->setIssuer('http://'.$_SERVER['HTTP_HOST'])
			->setAudience('http://'.$_SERVER['HTTP_HOST'])
			// Définition Hash User
			->setId(Self::idfyUser($user), true)
			// Définition Id User
			->set('uid', $user->id)
			// Définition de l'heure de création
			->setIssuedAt(time())
			// Définition de l'heure de départ
			->setNotBefore(time())
			// Définition de l'heure d'Expiration'
			->setExpiration(time() + Self::$expireDelay)
			// Génération du Token
			->getToken()
		;


		if (!Self::validateToken($user, $token)) {
			return false;
		}

		// Enregistrement Du Token En Base
		$tokenMdl->user = $user->id;
		$tokenMdl->key = (string) $token;

		$tokenMdl->save();

		return $tokenMdl->key;
	}

	/**
	 * Récupère le Token Depuis les Headers
	 * @param  RequestUtil $request La Requete
	 * @return UsersToken          Le Token Ou Null Si In-Existant
	 */
	public static function getFromHeader($request) {
		$auth = $request->header("Authorization");
    	if (!$auth) {
			$auth = $request->input("token");
	    	if (!$auth) {
	    		return false;
	    	}
    	}

    	$token = (new TokenParser())->parse($auth);
    	return UsersToken::where('user', $token->getClaim('uid'))->first();
	}
}
