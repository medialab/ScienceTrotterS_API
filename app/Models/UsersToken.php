<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Builder as TokenBuilder;
use Lcobucci\JWT\Token;

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
	
	public static function generateToken(Users $user, UsersToken $tokenMdl=null) {
		if (is_null($tokenMdl)) {
			$tokenMdl = new UsersToken([], $user);
		}


		$token = (new TokenBuilder())
			->setIssuer('http://'.$_SERVER['HTTP_HOST'])
			->setAudience('http://'.$_SERVER['HTTP_HOST'])
			->setId($user->id, true)
			->setIssuedAt(time())
			->setNotBefore(time() + 20)
			->setExpiration(time() + Self::$expireDelay)
			->getToken()
		;

		$tokenMdl->key = (string) $token;
		var_dump("Generated Token: ".$tokenMdl->key);

		$tokenMdl->save();

		return $tokenMdl;
	}
}
