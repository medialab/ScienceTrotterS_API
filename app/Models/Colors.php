<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\ModelUtil;

class Colors extends Model
{
	public $timestamps = false;
    protected $table = 'colors';

    protected $casts = [
        'id' => 'string',
    ];

    protected $fillable = ['color','name'];
}
