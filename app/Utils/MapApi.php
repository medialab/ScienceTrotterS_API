<?php

namespace App\Utils;

class MapApiUtil {
	private $bInit = false;

	private $sKey = false;
	private $sUrl = 'https://api.openrouteservice.org/directions';

	function __construct() {
		if (defined('MAP_API_KEY')) {
			$this->sKey = MAP_API_KEY;
		}
	}

	/**
	 * Récupération Du Temps + Distance de Trajet
	 * @param  Interests $oInt1 Premier Point
	 * @param  Interests $oInt2 Deuxième Point
	 * @return Bool | Array        False Si Echec || [
	 *     'time' => Int (Temps en secondes),
	 *     'distance' => Int (Distance en Mètres)
	 * ]
	 */
	public function getDistance($oInt1, $oInt2) {
		$aData = [
			'instructions' => false,
			'geometry' => false,
			'preference' => 'recommended',
			'unit' => 'm',
			'profile' => 'foot-walking',
			'api_key' => $this->sKey,
			'coordinates' => 
				$oInt1->geoloc->longitude.','.$oInt1->geoloc->latitude.'|'.
				$oInt2->geoloc->longitude.','.$oInt2->geoloc->latitude,
		];

		$c = new CurlMgrUtil($this->sUrl, $aData);
		$c->setMethod('get');		
		$result = json_decode($c->exec());

		/*var_dump($oInt1->geoloc);
		var_dump($oInt2->geoloc);
		var_dump($result);
		exit;*/

		if (is_null($result) || !empty($result->error)) {
			return false;
		}

		return $result->routes[0]->summary;
	}
}