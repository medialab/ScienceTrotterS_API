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

		if (is_null($result) || !empty($result->error)) {
			return false;
		}

		return $result->routes[0]->summary;
	}
}