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
			'unit' => 'm',
			'instructions' => 'false',
			'profile' => 'foot-walking',
			'preference' => 'recommended',
			
			'geometry' => 'true',
			
			'geometry_simplify' => 'true',
			'geometry_format' => 'geojson',
			
			'api_key' => $this->sKey,
			'coordinates' => 
				$oInt1->geoloc->longitude.','.$oInt1->geoloc->latitude.'|'.
				$oInt2->geoloc->longitude.','.$oInt2->geoloc->latitude,
		];

		$c = new CurlMgrUtil($this->sUrl, $aData);
		$c->setMethod('get');

		$apiResponse = $c->exec();
		$result = json_decode($apiResponse);

		/*var_dump($result);
		exit;*/

		if (is_null($result) || !empty($result->error)) {
			$this->logError($c, $aData, $result);
			return false;
		}

		unset($result->info);
		
		$aResult = [
			'api_response' => json_encode($result),
			'time' => &$result->routes[0]->summary->duration,
			'distance' => &$result->routes[0]->summary->distance,
		];

		return $aResult;
	}


	/**
	 * Écrit Dans les Logs du Web Server actif (Apache/ngix)
	 */
	private function logError(CurlMgrUtil $c, $aData, $aResponse) {
		$sDate = date('Y-m-d H:i:d');

		$aInfos = $c->getInfos();
		$aErrors = $c->getError();
		$sParams = var_export($aData, true);

		$sMapCode =  $aResponse->error->code;
		$sMapMsg =  $aResponse->error->message;

		$sMsg = "
			============== API: {$sDate} ==============
				Type: Fail To Execute A Request From Our Api To OpenMapService Api

				++++ Self Error:
					-- method: {$_SERVER['REQUEST_METHOD']}
					-- Url: {$_SERVER['REQUEST_URI']}

				++++ Curl Error:
					-- Code: {$aErrors['code']}
					-- Message: {$aErrors['err']}

				++++ MaiApi-Request:
					-- Method: Get
					-- Url: {$aInfos['url']}
					-- Http-Code: {$aInfos['http_code']}
					-- Response Code: {$sMapCode}
					-- Response Message: {$sMapMsg}
					-- Params: {$sParams}
		";

		$sMsg = preg_replace("/\t{3}/", "", $sMsg);
		error_log($sMsg);
	}
}