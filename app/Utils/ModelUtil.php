<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;

class ModelUtil extends Model
{
	private $sCurLang = false; // Langue Séléctionnée

	protected $userStr = 'Model';
	
	protected $aTranslateVars = []; // les Variables à traduire
	protected $aSkipPublic = ['created_at', 'state', 'sCurLang'];
	protected $aUploads = ['image', 'audio'];

	public function newCollection(array $models = []) {
		return new TranslateCollection($models);
	}

	/**
	 * Mets à jour une variable traductible pour une langue
	 * @param String $sVar  Nom de la variable
	 * @param mixed $value valeur de la variable
	 */
	private function setValueByLang($sVar, $value) {
		$sLang = $this->sCurLang;

		//var_dump("SETTING VAL BY LANG $sLang");
		
		if ($value !== false && empty($value)) {
			$value = null;
		}

		if (empty($this->attributes[$sVar])) {
			$var = new \StdClass;
		}
		else{
			$var = $this->attributes[$sVar];
		}

		//var_dump("Cur VAL", $var);
		//var_dump("New VAL", $value);

		// Si la valeur actuelle est une string on la décode
		if(is_string($var)) {
			$var = json_decode($var);
		}
		//var_dump("Cur VAL Decoded", $var);


		$var->$sLang = $value;
		
		$this->attributes[$sVar] = $var;
		//var_dump("Result: ", $this->attributes[$sVar]);
	}

	/**
	 * Mets à jour une variable traductible pour toutes les langues
	 * @param String $sVar  Nom de la variable
	 * @param mixed $value Valeur de la variable
	 */
	private function setValueAsJson($sVar, $value) {
		if (empty($this->attributes[$sVar])) {
			$var = new \StdClass;
		}
		else{
			$var = $this->attributes[$sVar];
		}

		if (empty($value)) {
			$var = null;
		}
		elseif (is_string($value)) {            
			$var = json_decode($value);
			
			if (is_null($var)) {
				throw new \Exception("Error: Can't Set Parcours::$sVar Due to Invalid Json: '$value'", 1);
			}
		}
		elseif (is_array($value)) {
			$var = (object) $value;
		}
		elseif(!is_object($value)) {
			throw new \Exception("Error: Can't Set Parcours::$sVar Due to Invalid Data Type. Accepted StdClass, Array, String (json) OR null", 1);
		}
		else{
			$var = $value;
		}

		$this->attributes[$sVar] = $var;
	}

	/**
	 *
	 */
	public function __get($sVar) {
		// Si il s'agit d'une variable De la BDD

		if (array_key_exists($sVar, $this->attributes)) {
			//var_dump("===== GETTING $sVar =====");

			// Si il s'agit d'une variable à traduire
			if (in_array($sVar, $this->aTranslateVars)) {
				//var_dump("-- As Translate");
				
				if (empty($this->attributes[$sVar])) {
					$var = new \StdClass;
				}
				else{
					$var = $this->attributes[$sVar];
				}


				// Si la valeur actuelle est une string on la décode
				if (is_string($var)) {
					//var_dump("-- Decoding Value", $var);
					$var = json_decode($var);

					// En cas d'échec on retourne NULL
					if (is_null($var)) {
						//var_dump("-- Decoding Faild");
						return null;
					}

					$this->attributes[$sVar] = $var;
				}

				//var_dump("-- Value: ", $var);
				// Si une langue est séléctionnée
				if ($this->sCurLang) {
					//var_dump("-- Get By Lang: {$this->sCurLang}");

					$sLang = $this->sCurLang;
					$res = empty($var->$sLang) ? null : $var->$sLang;;
					//var_dump($res);

					return $res;
				}

				//var_dump("-- Get As Json: ", $var);
				return $var;
			}

			return $this->attributes[$sVar];
		}
		elseif(in_array($sVar, $this->fillable)){
			return null;
		}

		return empty($this->$sVar) ? null : $this->$sVar;
	}

	

	function __set($sVar, $value) {
		//var_dump("==== SETTING $sVar ====", $value);
		// Si il s'agit d'une variable à traduire
		if (in_array($sVar, $this->aTranslateVars)) {
			//var_dump("-- As Translate");
			
			
			// Si une langue est choisie on met à jour que celle ci
			if ($this->sCurLang) {
				//var_dump("-- Setting For Lang: {$this->sCurLang}");
				$this->setValueByLang($sVar, $value);
			}
			// Si aucune langue est choisie on les met toutes à jour
			else{
				//var_dump("-- Setting For All Langs");
				$this->setValueAsJson($sVar, $value);
			}
		}
		elseif(in_array($sVar, $this->attributes) || in_array($sVar, $this->fillable)){
			$this->attributes[$sVar] = $value;
		}
		elseif(property_exists($this, $sVar)){
			$this->$sVar = $value;
		}
		else{
			throw new \Exception("Error: Try To Set $sVar In Model", 1);
		}

		//var_dump("RESULT: ", $this->$sVar);
	}

	
	// Définis la langue de l'objet
	public function setLang($l = false) {
		$this->sCurLang = $l;
	}

	/**
	 * Override de la fonction afin d'implémenter les traductions
	 * @return Array données de l'objet
	 */
	public function toArray($bAdmin=false) {
		$aResult = [];
		$sLang = $this->sCurLang;

		//var_dump("====== To Array: $sLang =====", $this->attributes);

		//var_dump("-- As Admin", $bAdmin);

		foreach ($this->attributes as $sVar => $value) {
			//var_dump("+++++++++ Var: $sVar", $value);
			
			if (in_array($sVar, $this->hidden) || (!$bAdmin && in_array($sVar, $this->aSkipPublic))) {
				//var_dump("-- Hidden Skip");
				continue;
			}

			if (in_array($sVar, $this->aTranslateVars)) {
				//var_dump("-- Translate Var");
				if (is_string($value)) {
					//var_dump("-- Decoding");
					$value = json_decode($value);
				}
				//var_dump($value);

				if (empty($value)) {
					//var_dump('-- Is Empty');
					$value = new \StdClass;
				}
				elseif($sLang){
					//var_dump("-- Selecting Lang $sLang");
					
					if (!$bAdmin) {
						//var_dump('-- Not Admin');
						
						if (empty($value->$sLang)) {
							//var_dump('-- Lang Empty');
							$aResult[$sVar] = (object) [$sLang => null];
						}
						else{
							//var_dump('-- Getting Data');
							$aResult[$sVar] = (object) [$sLang => $value->$sLang];
						}
					}
					else{
						//var_dump('-- As Admin');
						
						if (empty($value->$sLang)) {
							//var_dump('-- Is Empty');
							$aResult[$sVar] = null;
						}
						else{
							//var_dump('-- Getting Data');
							$aResult[$sVar] = $value->$sLang;
						}
					}
					
				}
				else{
					//var_dump('-- Getting Full Data');
					$aResult[$sVar] = $value;
				}
			}
			else{
				if ($sVar === "geoloc") {
					// var_dump("==== GETTING GEOLOC ====", $value);
					if (is_string($value)) {
						$value = json_decode($value);
					}

					// var_dump($value);
					//$aGeo = explode(';', $value);
					if (!$bAdmin) {
						$aResult['geo'] = [
							'latitude' => empty($value->latitude) ? 0 : $value->latitude,
							'longitude' => empty($value->longitude) ? 0 : $value->longitude
						];
					}
					else{
						if (empty($value)) {
							$aResult['geoloc'] = null;
						}
						else{
							$aResult['geoloc'] = $value->latitude.';'.$value->longitude;
						}
					}

					// var_dump($value);
				}
				else{
					$aResult[$sVar] = $value;
				}
			}

			/*if (array_key_exists($sVar, $this->getCasts()) && $this->getCasts()[$sVar] === 'json') {
				if (is_string($value)) {
					$value = json_decode($value);
				}

				if (in_array($sVar, $this->aTranslateVars)) {
					if ($sLang) {
						if (empty($value) || (!isset($value->$sLang))) {
							$value = null;
						}
						elseif ($bAdmin || static::$bAdmin){
							$aResult[$sVar] = $value->$sLang;
						}
						else{
							$aResult[$sVar] = (object) [$sLang => $value->$sLang];
						}
					}
					else{
						$aResult[$sVar] = $value;
					}
				}
				else{
					$aResult[$sVar] = $value;
				}
			}
			else{
				if ($sVar === "geoloc") {
					exit;
					$aGeo = explode(';', $value);
					$aResult['geo'] = [
						'latitude' => empty($aGeo[0]) ? 0 : $aGeo[0],
						'longitude' => empty($aGeo[1]) ? 0 : $aGeo[1]
					];
				}
				
				$aResult[$sVar] = $value;
			}*/
		}

		if ($bAdmin) {
			//var_dump("-- Adding Lang");
			$aResult['sCurLang'] = $sLang;
			//var_dump("-- Adding Force Lang");
			$aResult['force_lang'] = empty($this->attributes['force_lang']) ? null : $this->attributes['force_lang'];
		}

		//var_dump("-- Result: ", $aResult);

		return $aResult;
	}

	public function save(Array $options=[]) {
		//var_dump("==== SAVING ====");
		foreach ($this->attributes as $sKey => $value) {
			//var_dump("-- $sKey: ", $value);
			if (
				//in_array($sKey, $this->aTranslateVars) ||
				(array_key_exists($sKey, $this->getCasts()) && 
					$this->getCasts()[$sKey] === 'json'
				)
			) {
				//var_dump("-- AS TRANS");
				if (!is_string($value)) {
					$this->attributes[$sKey] = json_encode($value);
				}
			}
			/*elseif ($sKey === 'geoloc') {
				//var_dump("TEST GEOLOC");
				if (empty($value) || count($value) != 2) {
					$this->attributes['geoloc'] = null;
				}
				else{
					$this->attributes['geoloc'] = [
						"latitude" => $value->latitude, 
						"longitude" => $value->longitude
					];
				}
			}*/
		}

		//var_dump("-- Result: ", $this->attributes);
		return Parent::save($options);
	}

	public function enable($b = true) {
		if (!$b) {
			$this->attributes['state'] = false;
			return true;
		}

		//var_dump("====== TEST ENABLE ======");
		
		$force = $this->force_lang;
		//var_dump("FORCE LANG", $force);
		$tmpLang = $this->sCurLang;
		$this->setLang(false);

		foreach ($this->fillable as $key) {
			if (in_array($key, ['state', 'force_lang'])) {
				continue;
			}

			$value = empty($this->attributes[$key]) ? null : $this->attributes[$key];

			//var_dump("-- key $key", $value);

			if (empty($value)) {
				//var_dump("-- Fail $key Is Empty", $value);
				$this->attributes['state'] = false;
				return false;
			}
			
			if (in_array($key, $this->aTranslateVars)) {
				if ($force) {
					if (empty($value->$force)) {
						//var_dump("-- Fail $key Is Empty For Force Lang: '$force'", $value);
						$this->attributes['state'] = false;
						return false;
					}
				}
				elseif(empty($value->fr) || empty($value->en)) {
					//var_dump("-- Fail $key Is Empty For Obne Lang", $value);
					$this->attributes['state'] = false;
					return false;
				}
			}
		}

		$this->attributes['state'] = true;
		return true;
	}

	public function updateData($aData) {
		//var_dump("=== Updating Object ===", $aData);
		foreach ($aData as $key => $value) {
			// Données à Ignorer lors de l'update
			if (in_array($key, ['id', 'created_at', 'updated_at', 'state'])) {
				continue;
			}
			if (in_array($key, $this->aUploads)) {
				if (empty($value)) {
					continue;
				}

				if($aData[$key] !== $this->$key) {
					$this->downloadImage($aData[$key]);
				}
			}

			$this->$key = $value;
		}

		//var_dump("--- TEST", $this->attributes);

		//var_dump("--- Change Enable");
		if (array_key_exists('state', $aData)) {
			$this->enable((bool)$aData['state']);
		}
		else{
			$this->enable($this->attributes['state']);
		}
	}

	private function downloadImage($sName) {
		/* On crée le dossier de l'image */
		$dir = dirname(UPLOAD_PATH.$sName);
		if (!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		/* Url De téléchargement de l'image */
		$imgUrl = ADMIN_URL.'upload/'.$sName;
		
		/* si l'image existe on la remplace */
		$sPath = UPLOAD_PATH.$sName;
		
		if (file_exists($sPath)) {
			unlink($sPath);
		}
		$b = file_put_contents($sPath, fopen($imgUrl, 'r'));
	}
}
