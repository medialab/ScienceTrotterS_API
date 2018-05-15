<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\UsersToken;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class Users extends Model implements Authenticatable
{
	use AuthenticableTrait;

	public $timestamps = false;
    protected $table = 'users';

    protected $casts = [
        'id' => 'string',
    ];

    protected $fillable = ['firstname','lastname','email','password'];

    protected $hidden = [
       'password'
	];

	public static function getByToken($token) {
		$token = UsersToken::where('id', $token)->first();
		if (!empty($token)) {
			return false;
		}

		$user = Users::where('id', $token->user);
		return $user;
	}
}
