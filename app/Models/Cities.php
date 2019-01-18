<?php

namespace App\Models;

use App\Utils\ModelUtil;

class Cities extends ModelUtil
{
	protected $table = 'cities';
	protected static $sTable = 'cities';
	protected $userStr = 'la ville';	// Nom du Model pour un utilisateur
	protected static $sChildTable = 'interests';	// Table Enfant. Utilisé pour le Context public

	public $timestamps = true;

	/**
	 * Liste Des traduction des propriétés
	 */
	protected $aProperties = [
		'title' => 'Titre',
		'state' => 'Status',
		'geoloc' => 'Géolocalisation',
		'image' => 'Image de Couverture',
	];

	/**
	 * Varialbles modifialbes en DB
	 */
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

	/**
	 * Types des colones particulières 
	 */
	protected $casts = [
	  'id' => 'string',
	  'title' => 'json',
	  'geoloc' => 'json'
	];

	protected $primaryKey = 'id';

	/**
	 * Variables à Traduire
	 */
	protected $aTranslateVars = [
	  //'title'
	];

	/**
	 * Variables à Traduire
	 */
	protected $aIgnoreTranslateVars = [
	  'title'
	];

	/**
	 * Retrourne une nouvelle instance vide
	 * @return Cities nouvelle instance
	 */
	public static function getInstance() {
		return new Cities;
	}

	public function __set($sVar, $value) {
		if ($sVar === 'title') {
			$this->attributes['title'] = (object) ['fr' => $value, 'en' => $value];
			return;
		}

		Parent::__set($sVar, $value);
	}

	/**
	 * Recherche une phrase dans tous les Parcours
	 * @param  String  $query   Recherche
	 * @param  Array  $columns  Colones à retoruner
	 * @return TranslateCollection Collection des Modeles
	 */
	public static function search($query, $columns, $order=false) {
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
