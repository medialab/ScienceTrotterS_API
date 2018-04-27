<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    public $timestamps = false;
    protected $table = 'user';
    protected $primaryKey = 'id';

    protected $fillable = [
        'firstname', 'lastname'
    ];

}
