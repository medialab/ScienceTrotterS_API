<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Builder as TokenBuilder;

use Lcobucci\JWT\Parser as TokenParser;
use Lcobucci\JWT\ValidationData;

class UsersToken extends Model
{
	private static $expireDelay = 3600;

	function __construct(array $attributes = [], Users $user=null) {
		Parent::__construct($attributes);

		if (!is_null($user)) {
			$this->user = $user->id;
		}
	}

	protected $table = 'users_token';

	protected $casts = [
			'id' => 'string',
	];

	protected $fillable = ['user','key'];

	protected $hidden = [
		'id'
	];

	public static function getExpireDelay() {
		return Self::$expireDelay;
	}

	public static function idfyUser(Users $user) {
		return md5($user->id.'-'.$user->created_at.'-'.$user->email);
	}
	
	public static function generateToken(Users $user, UsersToken $tokenMdl=null) {
		if (is_null($tokenMdl)) {
			$tokenMdl = new UsersToken([], $user);
		}

		$token = (new TokenBuilder())
			->setIssuer('http://'.$_SERVER['HTTP_HOST'])
			->setAudience('http://'.$_SERVER['HTTP_HOST'])
			->setId(Self::idfyUser($user), true)
			->set('uid', $user->id)
			->setIssuedAt(time())
			->setNotBefore(time())
			->setExpiration(time() + Self::$expireDelay)
			->getToken()
		;

		$tokenMdl->user = $user->id;
		$tokenMdl->key = (string) $token;
		// var_dump("Generated Token: ".$tokenMdl->key);
		// var_dump("Generated ID: ".Self::idfyUser($user));

		$tokenMdl->save();

		return $tokenMdl->key;
	}

	public static function getFromHeader($request) {
		$auth = $request->header("Authorization");
    	if (!$auth) {
    		return false;
    	}

    	$token = (new TokenParser())->parse($auth);
    	return UsersToken::where('user', $token->getClaim('uid'))->first();
	}
}
