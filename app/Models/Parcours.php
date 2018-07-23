<?php

namespace App\Models;

use App\Utils\ModelUtil;
use App\Models\InterestWay;

class Parcours extends ModelUtil
{
	public $timestamps = true;
	protected $table = 'parcours';

	/**
	 * Nom du Model pour un utilisateur
	 */
	protected $userStr = 'le parcours';

	/**
	 * Table Enfant. Utilisé pour le Context public
	 */
	protected static $sChildTable = 'interests';


	/**
	 * Liste Des traduction des propriétés
	 */
	protected $aProperties = [
		'cities_id' => 'Ville',
		'title' => 'Titre',
		'state' => 'Status',
		'time' => 'Durée',
		'color' => 'Couleur',
		'audio' => 'Audio',
		'description' => 'Déscription',
		'audio_script' => 'Script Audio',
	];

	/**
	 * Varialbles modifialbes en DB
	 */
	protected $fillable = [
	  'id',
	  'cities_id',
	  'title',
	  'time',
	  'audio',
	  'description',
	  'audio_script',
	  'force_lang',
	  'state',
	  'color',
	  'created_at',
	  'updated_at'
	];

	/**
	 * Types des colones particulières 
	 */
	protected $casts = [
	    'id' => 'string',
	    'title' => 'json',
	    'time' => 'json',
	    'audio' => 'json',
	    'description' => 'json',
	    'audio_script' => 'json'
	];

	protected $primaryKey = 'id';

	/**
	 * Variables à Traduire
	 */
	protected $aTranslateVars = [
	  'title', 
	  'time', 
	  'audio', 
	  'description',
	  'audio_script'
	];

	public $interestsList = [];
	public $distance = 0;

	/**
	 * Retrourne une nouvelle instance vide
	 * @return Parcours nouvelle instance
	 */
	public static function getInstance() {
		return new Parcours;
	}

	/**
	 * Réécritue Récupération de variable
	 * @param  String $sVar Nom de la variable
	 * @return Mixed       La variable ou NULL
	 */
	public function __get($sVar) {
		switch ($sVar) {
			case 'interests':
				return $this->getInterests();
				break;

			case 'city':
				$this->loadParents();
				return $this->attributes['city'];
				break;
			
			default:
				return Parent::__get($sVar);
				break;
		}
	}

	/**
	 * Insert / Update Model
	 * @param  Array|array $options Options Lumen
	 * @return Bool               Success
	 */
	public function save(Array $options=[]) {
		$bPrevState = (bool) @$this->original['state'];
		$bSuccess = Parent::save($options);
		
		$bCurState = $this->attributes['state'];

		if (!$bSuccess) {
			return false;
		}

		$this->loadParents();
		if (!empty($this->city)) {
			$this->city->defineLang('fr');
			$parcLang = $this->force_lang;
			$cityLang = $this->city->force_lang;
			
			
			/*
			var_dump("CityForce: ". $cityLang);
			var_dump("ParcoursForce: ". $parcLang);
			var_dump("Diff: ". $cityLang !== $parcLang);
			*/

			$this->city->defineLang('fr');
			

			if ($cityLang && $parcLang && $cityLang !== $parcLang) {
				$title = $this->city->title;
				if (is_object($title)) {
					$title = array_values(get_object_vars($title));
					if (empty($title)) {
						$title = '';
					}
					else {
						$title = $title[0];
					}
				}

				$aLangs = ['fr' => 'français', 'en' => 'anglais'];

				$this->errorMsg = 'Attention: La ville: '.$title.' est en '.$aLangs[$cityLang].' uniquement, alors que ce parcours est en '.$aLangs[$parcLang].' uniquement';
			}
			
			//var_dump("Msg: ". $this->errorMsg);
		}

		unset($this->city);
		unset($this->attributes['city']);
		/*exit;*/

		return $bSuccess;
	}

	public function getFirstInterest() {
		$oMin = null;
		$oMax = false;
		$dMinScore = 0;
		$dMaxScore = 0;
		
		// Recherche d'un point de départ le plus à l'extrême
		foreach ($this->interests as $key => $oInt) {
			if (!$oInt->state) {
				continue;
			}

			$oInt->setLang('fr');
			
			$geoloc = $oInt->geoloc;
			$dNewScore = $geoloc->latitude + $geoloc->longitude;

			if ($dMinScore === 0 || $dNewScore < $dMinScore) {
				$oMin = $oInt;
				$dMinScore = $geoloc->latitude + $geoloc->longitude;
			}
			/*elseif ($dMaxScore === 0 || $dNewScore > $dMaxScore) {
				$oMax = $oInt;
				$dMaxScore = $geoloc->latitude + $geoloc->longitude;
			}*/
		}

		return $oMin;
	}

	/**
	 * Calcule la distance + le time les plus courts pour suivre le parcours
	 * @param  String $parcId Id du parcours
	 * @return Array         [
	 *     'pointCnt' => nombre De points à parcourir
	 *     'distance' => Distance à parcourir en Mètres
	 *     'time' => [
	 *         'string' => Heure Sous Forme: 5h 30min
	 *         'h' => Nombre d'Heures
	 *         'm' => Nombre de Minutes
	 *     ]
	 * ]
	 */
	public function getLength() {
		/*$cnt = 1;
		$oFirst = $this->getFirstInterest();
		if (!$oFirst) {
			return $aRes = [
				'pointCnt' => 0,
				'distance' => 0,
				'time' => [
					'string' => 0,
					'h' => 0,
					'm' => 0,
					'totSec' => 0
				]
			];
		}*/


		$aRes = $this->getOptimizedTrace();
		return $aRes['length'];
		exit;

		$totalTime = 0;
		$totalLength = 0;

		$aPrevious = [];
		$oCurrent = $oFirst;

		// Calcule des Distances
		while(!is_null($oNext = InterestWay::closest($oCurrent, $aPrevious))) { // Récupération Du point le plus proche
			$oNext->setLang('fr');
			$aPrevious[] = $oCurrent->id;

			// Si Désactivé, on continue
			if (!$oNext->state) {
				$oCurrent = $oNext;
				continue;
			}


			// On Charge la distance entre les 2 points
			$oWay = InterestWay::byInterests($oCurrent, $oNext);
			if (is_null($oWay)) {
				$oCurrent = $oNext;
				continue;
			}

			$cnt++;
			$totalTime += $oWay->time;
			$totalLength += $oWay->distance;
			$oCurrent = $oNext;
		}

		// On arrondit pour retirer les secondes 
		$totSec = $totalTime;
		$totalTime = round($totalTime / 1) * 1;

		$sTime = date('H:i:s', $totalTime);
		$h = explode(':', $sTime);
		$m = $h[1];
		$s = $h[2];
		$h = $h[0];
		$sTime = $h.'h '.$m.'min';


		$aRes = [
			'pointCnt' => $cnt,
			'distance' => round($totalLength / 1000, 3),
			'time' => [
				'string' => $sTime,
				'h' => (int) $h,
				'm' => (int) $m,
				'totSec' => (int) $totSec
			]
		];

		return $aRes;
	}

	/**
	 * Charge la ville du parcours
	 * @return Cities Model de la Ville
	 */
	public function loadParents() {
		if (empty($this->attributes['cities_id'])) {
			$this->attributes['city'] = null;
			return;
		}

		$city = Cities::Where('id', $this->attributes['cities_id'])->get(['id', 'title', 'state', 'force_lang'])->first();
		if (!empty($city)) {
			$city->setLang($this->getLang());
		}

		$this->attributes['city'] = $city;
	}

	/**
	 * Récupération des points d'interets
	 * @return Interests Model Du Point
	 */
	public function getInterests() {
		if (!empty($this->attributes['interests'])) {
			return $this->attributes['interests'];
		}

		$oModelList = Interests::Where('parcours_id', $this->attributes['id']);
		$this->attributes['interests'] = $oModelList->get();

		$this->attributes['interests']->setLang($this->getLang());

		return $this->attributes['interests'];
	}

	/**
	 * Recherche une phrase dans tous les Parcours
	 * @param  String  $query   Recherche
	 * @param  Array  $columns  Colones à retoruner
	 * @param  Array  $order    Ordre de retour
	 * @return TranslateCollection Collection des Modeles
	 */
	public static function search($query, $columns, $order = false) {
		// ECHAPPEMENT DES QUOTES
		$query = preg_replace("/('{1})/", ("''"), $query);
		$oModel = new Parcours;

		if (!empty($columns)) {
			foreach ($columns as &$col) {
				$col = 'parcours.'.$col;
			}
		}
		else{
			$columns = 'parcours.*';
		}

		$list = Parcours::Select($columns)
			->leftJoin('cities', 'parcours.cities_id', '=', 'cities.id')
			->whereRaw( // Recherche dans le Tite
				"CONCAT(parcours.title->>'fr', parcours.title->'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				// Recherche dans le sript audio
				"CONCAT(parcours.audio_script->>'fr', parcours.audio_script->'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				// Recherche dans le Titre de la ville
				"CONCAT(cities.title->>'fr', cities.title->'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				// Recherche dans description
				"CONCAT(parcours.description->>'fr', parcours.description->'en') ILIKE '%{$query}%'"
			)
		;

		if (is_array($order) && count($order) == 2) {
			$orderCol = $order[0];
			$orderWay = $order[1];
		}
		else{
			$order = false;
		}

		// Tris pard défaut Title
		if (!$order || ($orderCol !== "score" && !in_array($orderCol, $oModel->fillable))) {
			$list->orderBy('parcours.title->fr', 'ASC');
		}
		// Tri Par Score
		elseif ($orderCol === 'score') {
			$list->orderByRaw(// Recherche dans le Tite => 20 points
				"
				(
					(CONCAT(parcours.title->>'fr', parcours.title->>'en') 
					ILIKE 
					'%".$query."%')::int * 20
				) +
				".
				// Recherche dans la déscription=> 15 points
				"(
					(CONCAT(parcours.description->>'fr', parcours.description->>'en') 
					ILIKE 
					'%".$query."%')::int * 14
				) +
				".
				// Recherche dans le Script Audio => 10 points
				"
				(
					(CONCAT(parcours.audio_script->>'fr', parcours.audio_script->>'en') 
					ILIKE 
					'%".$query."%')::int * 10
				) +
				".
				// Recherche dans le Titre de la Ville => 3 points
				"(
					(CONCAT(cities.title->>'fr', cities.title->>'en') 
					ILIKE 
					'%".$query."%')::int * 5
				)

				".$orderWay."
			");
		}
		// Tris par Colone demandée
		else {
			if (!in_array($orderCol, $oModel->aTranslateVars)) {
				$list->orderBy('parcours.'.$orderCol, $orderWay);
			}
			else{
				$list->orderBy('parcours.'.$orderCol.'->fr', $orderWay);
			}
		}

		return $list->get();
	}

	public function getOptimizedTrace(Interests $oFirst=null, $sLang=false, $columns=null, $bAdmin=false) {
		// Si Aucun Parcours de Départ Sélectionné On prend Le Premier
		if (is_null($oFirst)) {
			$oFirst = $this->getFirstInterest();
		}

		if (is_null($oFirst)) {
			return [
				'interests' => [],

				'length' => [
					'pointCnt' => 0,
					'distance' => 0,
					'time' => [
						'string' => '00:00',
						'h' => 0,
						'm' => 0,
						'totSec' => 0
					]
				]
			];
		}

		$aResults = Interests::optimizeOrder($oFirst, $sLang, $columns, $bAdmin);

		$res = $aResults['best'];
		$sTime = date('H:i', $res['time']);
		$h = explode(':', $sTime);
		$m = $h[1];
		$h = $h[0];

		return [
			'interests' => $res['interests'],

			'length' => [
				'pointCnt' => count($res['interests']),
				'distance' => $res['distance'],
				'time' => [
					'string' => $sTime,
					'h' => $h,
					'm' => $m,
					'totSec' => $res['time'],
				]
			]
		];
	}

	public static function closest($aGeo, $parcId, $cityId, $sLang=false, $columns=false, $aOrder=false) {
		$oParcoursList = Parcours::Select(['parcours.*']);
		
		$oParcoursList->where([['cities_id', '=', $cityId], ['parcours.state', '=', true]]);
		$oParcoursList->leftJoin('cities', 'parcours.cities_id', '=', 'cities.id');

		if ($sLang) {
			$oParcoursList->where(function($query) use ($sLang) {
				$query->Where('parcours.force_lang', '')
					->orWhereNull('parcours.force_lang')
					->orWhere('parcours.force_lang', $sLang);
			});
			
			$oParcoursList->where(function($query) use ($sLang) {
				$query->Where('cities.force_lang', '')
					->orWhereNull('cities.force_lang')
					->orWhere('cities.force_lang', $sLang);
			});
		}

		$oParcoursList = $oParcoursList->get($columns);
		if (!count($oParcoursList)) {
			return false;
		}

		if ($columns && !in_array('geoloc', $columns)) {
			$columns[] = 'geoloc';
		}

		$aResults = [];
		$dBestDistance = -1;
		$oParcSelected = false;
		$oInterestSelected = false;
		foreach ($oParcoursList as $oParc) {
			$oParc->setLang($sLang);

			$oInt = Interests::closest($aGeo, $oParc->id, false, $sLang, $columns);
			
			if (is_null($oInt)) {
				continue;
			}

			$oInt->setLang($sLang);
			$distance = abs($oInt->geoloc->latitude - $aGeo[0]) + abs($oInt->geoloc->longitude - $aGeo[1]);

			$oParc->distance = $distance;
			$aResults[$distance * 10000] = $oParc;			
			if ($dBestDistance == -1 || $distance < $dBestDistance) {
				$oParcSelected = $oParc;
				$dBestDistance = $distance;
				$oInterestSelected = $oInt;
			}
		}
		
		if (!$oParcSelected) {
			return false;
		}


		if ($aOrder && $aOrder[0] !== 'distance') {
			usort($aResults, function($a, $b) use ($aOrder, $sLang) {
				$fact = $aOrder[1] === 'desc' ? -1 : 1;

				$a->defineLang($sLang);
				$b->defineLang($sLang);

				$cmp = strcmp($a->title, $b->title);
				if (!$cmp) {
					if ($a->distance == $b->distance) {
						return 0;
					}

					return $fact * ($a->distance < $b->distance ? -1: 1);
				}

				return $fact * $cmp;
			});
		}
		else{
			usort($aResults, function($a, $b) use ($aOrder, $sLang) {
				if (!empty($aOrder)) {
					$fact = $aOrder[1] === 'desc' ? -1 : 1;
				}
				else{
					$fact = 1;
				}

				$a->defineLang($sLang);
				$b->defineLang($sLang);
				
				if ($a->distance == $b->distance) {
					return $fact * strcmp($a->title, $b->title);
				}

				return $fact * (($a->distance < $b->distance) ? -1 : 1);
			});
		}

		return array_values($aResults);
	}
}