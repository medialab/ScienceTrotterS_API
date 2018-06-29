<?php

namespace App\Models;

use App\Utils\ModelUtil;

class Cities extends ModelUtil
{
	protected $table = 'cities';
	protected $userStr = 'la ville';

	public $timestamps = true;

	protected $fillable = [
	  'id',
	  'title',
	  'image',
	  'geoloc',
	  'force_lang',
	  'state',
	  'created_at',
	  'updated_at'
	];

	protected $casts = [
	  'id' => 'string',
	  'title' => 'json',
	  'geoloc' => 'json'
	];

	protected $primaryKey = 'id';

	protected $aTranslateVars = [
	  'title'
	];


	public static function getInstance() {
		return new Cities;
	}

	public static function search($query, $columns) {
		$query = preg_replace("/('{1})/", ("''"), $query);
		/*$list =  Cities::Where([
					["title->fr", 'ILIKE', "%".$query."%"]
				])*/
		$list =  Cities::WhereRaw("CONCAT(title->>'fr', title->>'en') ILIKE '%{$query}%'")
				->orderBy("title->fr")
				->get($columns)
		;

		return $list;
	}
}
