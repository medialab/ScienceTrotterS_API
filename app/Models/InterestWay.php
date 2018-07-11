<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Utils\ModelUtil;
use App\Utils\MapApiUtil;

class InterestWay extends ModelUtil
{
	public $timestamps = false;
	protected $primaryKey = 'id';
	protected $table = 'interest_way';

	private static $tmpDeleted = false; // Tableau des Trajets à Supprimer

	/**
	 * Types des colones particulières 
	 */
	protected $casts = [
		'int1' => 'string',
		'int2' => 'string',
		'time' => 'float',
		'distance' => 'float'
	];


	/**
	 * Varialbles modifialbes en DB
	 */
	protected $fillable = [
		'int1',
		'int2',
		'time',
		'distance'
	];

	/**
	 * Retrourne une nouvelle instance vide
	 * @return InterestWay nouvelle instance
	 */
	public static function getInstance() {
		return new InterestWay;
	}

	/**
	 * Retourne le point d'interet le plus proche de la cible
	 * @param  String | Interests  $oInt     (string) Id Point | Model\Interests
	 * @param  Array $aIgnores Tableau d'IDs de Points à ignorer
	 * @return ModelCollection            Colection des résultats
	 */
	public static function closest($oInt, $aIgnores=false) {
		// Récupération de l'id
		$id = is_string($oInt) ? $oInt : $oInt->id;

		// Where l'id du Point Cible
		$oModelList = InterestWay::Where(function($query) use ($id) {
			$query->where('int1', $id)->orWhere('int2', $id);
		});

		if ($aIgnores) {
			// Exclusion des ignores
			$oModelList->where(function($query) use ($aIgnores) {
				$query->whereNotIn('int1', $aIgnores)->whereNotIn('int2', $aIgnores);
			});
		}

		// Limit + Tris Par Distances
		$oModelList
			->limit(1)
			->orderBy('distance', 'ASC')
		;

		// Récupération Du Plus Proche
		$oWay = $oModelList->get()->first();
		if (is_null($oWay)) {
			return null;
		}

		// Récupération Du Model du Point
		$intId = $id !== $oWay->int1 ? $oWay->int1 : $oWay->int2;
		return Interests::Where('id', $intId)->get()->first();
	}

	/**
	 * Récupération des trajets d'un Point
	 * @param  String | Interests  $oInt     (string) Id Point | Model\Interests
	 * @return ModelCollection       Collection des résultats
	 */
	public static function byInterest($oInt) {
		$id = is_string($oInt) ? $oInt : $oInt->id;
		$oModelList = InterestWay::Where('int1', $id)->orWhere('int2', $id);

		return $oModelList->get();
	}

	/**
	 * Récupération d'un' trajets d'un Couple de Points
	 * @param  S $oInt1 [description]
	 * @param  String | Interests  $oInt1     (string) Id Point | Model\Interests
	 * @param  String | Interests  $oInt2     (string) Id Point | Model\Interests
	 * @return InterestWay        Model du Trajet
	 */
	public static function byInterests($oInt1, $oInt2) {
		$id1 = is_string($oInt1) ? $oInt1 : $oInt1->id;
		$id2 = is_string($oInt2) ? $oInt2 : $oInt2->id;
		
		$oModelList = InterestWay::Where(function($query) use ($id1) {
			$query->where('int1', $id1)
				->orWhere('int2', $id1)
			;
		});

		$oModelList->where(function($query) use ($id2) {
			$query->Where('int1', $id2)
				->orWhere('int2', $id2)
			;
		});
		
		return $oModelList->get()->first();
	}

	/**
	 * Mets à jours tout les trajets liés à un Point
	 * @param  String | Interests  $oInt     (string) Id Point | Model\Interests
	 * @return Array       tableau des nouveaux trajets
	 */
	public static function updateByInterest($oInt) {
		// Récupération du parcours
		$oParc = $oInt->loadParcours();

		if (is_null($oParc)) {
			//var_dump("Parc Is NULL");
			return [];
		}

		$aDistances = [];
		$mapApi = new MapApiUtil();
		// Récupération de la liste de Points
		$aInterests = $oParc->getInterests();

		// On Recalcule les Distances
		foreach ($aInterests as $oInt2) {
			if ($oInt->id === $oInt2->id) {
				// var_dump("Same Skip");
				continue;
			}
			elseif(empty($oInt2->geoloc)) {
				// var_dump("Geo Empty", $oInt2->geoloc);
				continue;
			}

			// Appel à L'api Open Route Service
			$aDist = $mapApi->getDistance($oInt, $oInt2);
			if ($aDist) {
				// var_dump("VALID DISTANCE");
				$aDist = [
					'time' => $aDist->duration, 
					'distance' => $aDist->distance
				];
			}
			else{
				// var_dump("FAIL DISTANCE");
				$aDist = [
					'time' => -1, 
					'distance' => -1
				];
			}

			// Récupération du Trajet
			$oWay = InterestWay::byInterests($oInt, $oInt2);
			if (is_null($oWay)) {
				// var_dump("New Way");
				// Créaation d'un nouveau Trajet
				$oWay = new InterestWay;
				$oWay->int1 = $oInt->id;
				$oWay->int2 = $oInt2->id;
			}

			// Remplissage des informations si il y a un changement
			if ($oWay->time !== $aDist['time'] || $oWay->distance !== $aDist['distance']) {
				$oWay->time = $aDist['time'];
				$oWay->distance = $aDist['distance'];

				// var_dump("Save Way");
				// var_dump($oWay->time);
				$b = $oWay->save();
			}

			$aDistances[$oWay->id] = $oWay;
			$oInt2->refresh();
		}

		return $aDistances;
	}

	/**
	 * Suprime les Trajets liés au Point
	 * @param  String | Interests  $oInt     (string) Id Point | Model\Interests
	 * @param  boolean $bCache Garder les modèles Suprimé en cache (Utilisé pour la récupération)
	 * @return ModelCollection          Collection des modèles Supprimés
	 */
	public static function deleteByInterest($oInt, $bCache=false) {
		$oWayList = InterestWay::byInterest($oInt);

		if ($bCache) {
			Self::$tmpDeleted = $oWayList;
		}

		$oWayList->delete();
		return $oWayList;
	}

	/**
	 * Restore les Trajets Supprimés en cache
	 * @return Bool Récupération réussie
	 */
	public static function restoreDeleted() {
		if (!Self::$tmpDeleted) {
			return true;
		}


		// var_dump("Restoring Old Ways");
		$b = Self::$tmpDeleted->save();
		if ($b) {
			Self::$tmpDeleted = false;
		}

		return $b;
	}

	public static function search($search, $columns, $order=false) { 
		return null; 
	}
}