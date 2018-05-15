<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lcobucci\JWT\Builder as TokenBuilder;

class UsersToken extends Model
{
	private static $expireDelay = 3600;

	function __construct(Users $user=null) {
		if (!is_null($user)) {
			$this->user = $user->id;
		}
	}

	public $timestamps = false;
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
	
	public static function generateToken(Users $user) {
		$tokenMdl = new UserToken($user);

		$token = (new TokenBuilder())
			->setIssuer('http://'.$_SERVER['HTTP_HOST'])
			->setAudience('http://'.$_SERVER['HTTP_HOST'])
			->setId($user->id, true)
			->setUid($user->id, true)
			->setIssuedAt(time())
			->setNotBefore(time() + 20)
			->setExpiration(time() + Self::$expireDelay)
			->getToken()
		;
		
		var_dump($token);
		var_dump($tokenMdl);
		exit;
	}
}
