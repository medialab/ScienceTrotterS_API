<?php

namespace App\Models;
use App\Utils\RequestUtil as Request;
use Illuminate\Database\Eloquent\Model;
use App\Utils\ModelUtil;

class Credits extends ModelUtil
{
	// Durée de Vie D'un Token
	protected $table = 'credits';

	/**
	 * Types des colones particulières 
	 */
	protected $casts = [
			'id' => 'string',
			'content' => 'json'
	];

	/**
	 * Varialbles modifialbes en DB
	 */
	protected $fillable = [
		'title',
		'css',
		'content',
		'state',
		'created_at',
		'updated_at'
	];

	/**
	 * Variable à ne pas récupérer
	 */
	protected $hidden = [
		'id'
	];

	public static function getInstance() {
		return new Credits;
	}

	/**
	 * Recherche une phrase dans tous les Parcours
	 * @param  String  $query   Recherche
	 * @param  Array  $columns  Colones à retoruner
	 * @return TranslateCollection Collection des Modeles
	 */
	public static function search($query, $columns, $order=false) {
		return null;
	}

	public static function list(Request $oRequest, $bAdmin=false) {
		$sLang = $oRequest->input('lang');
		$oModel = static::getInstance();

		$sTable = $oModel->table;
		$oModelList = Self::Take(1000);

		if (!$bAdmin) {
			$oModelList->where('state', true);
		}

		return $oModelList;
	}
}
