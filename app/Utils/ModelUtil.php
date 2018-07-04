<?php

namespace App\Utils;

use App\Utils\RequestUtil as Request;
use Illuminate\Database\Eloquent\Model;

abstract class ModelUtil extends Model
{
	private $sCurLang = false; // Langue Séléctionnée

	protected $userStr = 'Model';
	protected static $sChildTable = false;
	
	protected $aTranslateVars = []; // les Variables à traduire
	protected $aSkipPublic = ['created_at', 'state', 'sCurLang'];
	protected $aUploads = ['image', 'audio'];

	protected $errorMsg = null;
	protected $aOptionalFields = [];
	protected $aProperties = [];

	public function getId() {
		return empty($this->attributes['id']) ? null : $this->attributes['id'];
	}

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

	public function __isset($sVar) {
		if (in_array($sVar, $this->fillable) || array_key_exists($sVar, $this->attributes)) {

			if (isset($this->casts[$sVar]) && $this->casts[$sVar] === 'json' && is_string($this->attributes[$sVar])) {
				$this->attributes[$sVar] = json_decode($this->attributes[$sVar]);
			}

			if (is_object($this->attributes[$sVar])) {
				return !empty(get_object_vars($this->attributes[$sVar]));
			}
			else{
				return !empty($this->attributes[$sVar]);
			}
		}
		
		return Parent::__isset($sVar);
	}

	/**
	 *
	 */
	public function __get($sVar) {
		// Si il s'agit d'une variable De la BDD

		if (in_array($sVar, $this->fillable) || array_key_exists($sVar, $this->attributes)) {
			//var_dump("===== GETTING =====");

			// Si il s'agit d'une variable à traduire
			if (in_array($sVar, $this->aTranslateVars) || (isset($this->casts[$sVar]) && $this->casts[$sVar] === 'json')) {
				// var_dump("-- As Translate");
				
				if (empty($this->attributes[$sVar])) {
					$var = new \StdClass;
				}
				else{
					$var = $this->attributes[$sVar];
				}


				// Si la valeur actuelle est une string on la décode
				if (is_string($var)) {
					// var_dump("-- Decoding Value", $var);
					$var = json_decode($var);

					// En cas d'échec on retourne NULL
					if (is_null($var)) {
						// var_dump("-- Decoding Faild");
						return null;
					}

					$this->attributes[$sVar] = $var;
				}

				// var_dump("-- Value: ", $var);
				// Si une langue est séléctionnée
				if (in_array($sVar, $this->aTranslateVars) && $this->sCurLang) {
					// var_dump("-- Get By Lang: {$this->sCurLang}");

					$sLang = $this->sCurLang;
					$res = empty($var->$sLang) ? null : $var->$sLang;;
					// var_dump($res);

					return $res;
				}

				// var_dump("-- Get As Json: ", $var);
				return $var;
			}

			return !isset($this->attributes[$sVar]) ? null : $this->attributes[$sVar];
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
		elseif(array_key_exists($sVar, $this->attributes) || in_array($sVar, $this->fillable)){
			if (isset($this->casts[$sVar]) && $this->casts[$sVar] === 'json') {
				$this->attributes[$sVar] = (object)$value;
			}
			else{
				$this->attributes[$sVar] = $value;
			}
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

	// Définis la langue de l'objet
	public function getLang() {
		return $this->sCurLang;
	}

	public static function getInstance() {
		return null;
	}

	public static function list(Request $oRequest, $bAdmin=false) {
		$skip = $oRequest->getSkip();
		$limit = $oRequest->getLimit();
		$sLang = $oRequest->input('lang');
		$aOrder = $oRequest->getOrder();
		$oModel = static::getInstance();

		$sTable = $oModel->table;

		/*$class = get_class();
		var_dump($class);
		exit;*/

		$oModelList = Self::Select($sTable.'.*');
		if ($sLang && !$bAdmin) {
		    $oModelList->where(function($query) use ($sLang, $bAdmin, $sTable){
	            $query->Where($sTable.'.force_lang', '')
	            	->orWhereNull($sTable.'.force_lang')
	            	->orWhere($sTable.'.force_lang', $sLang);
		    });
		}

		if (!$bAdmin) {
			if (static::$sChildTable) {
				$sChild = static::$sChildTable;
				
				$childColumn = static::$sChildTable.'.';
				$childColumn .= $oModel->table;
				$childColumn .= '_id';

				$oModelList->leftJoin($sChild, function($query) use ($childColumn, $sChild, $sTable, $sLang) {
					$query->on($sTable.'.id', '=', $childColumn);
					/*$query->where($sChild.'.state', '=', true);*/

					$query->Where(function($query) use ($sChild, $sLang) {
						$query->Where($sChild.'.force_lang', '')
							  ->orWhereNull($sChild.'.force_lang')
						;

						if ($sLang) {
							$query->orWhere($sChild.'.force_lang', $sLang);
						}
					});
				});

				$oModelList->groupBy($sTable.'.id');
				$oModelList->whereNotNull($sChild.'.id');
			}

		    $oModelList->where($sTable.'.state', true);
		}

		if ($aOrder && count($aOrder) === 2) {
		    $aOrderCol = $aOrder[0];
		    $sOrderWay = $aOrder[1];

		    if (!is_array($aOrderCol)) {
		        $aOrderCol = [$aOrderCol];
		    }

		    foreach ($aOrderCol as $sOrderCol) {
		    	$sColName = explode('.', $sOrderCol);
		    	$sColName = $sColName[count($sColName)-1];

		        if (in_array($sColName, $oModel->aTranslateVars)) {
		            if ($oModel->force_lang) {
		                $sOrderCol .= '->'.$oModel->force_lang.'' ;
		            }
		            elseif ($sLang) {
		                $sOrderCol .= '->'.$sLang.'' ;
		            }
		            else{
		                $sOrderCol .= '->fr' ;
		            }
		        }

		        $oModelList->orderBy($sOrderCol, $sOrderWay);
		    }
		}

		$oModelList->take($limit)->skip($skip);
		/*var_dump($oModelList->toSql());
		var_dump($oModelList->get());
		exit;*/

		return $oModelList;
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

			if ($value instanceOf ModelUtil) {
				$aResult[$sVar] = $value->toArray($bAdmin);
				continue;
			}

			if (in_array($sVar, $this->aTranslateVars) || (array_key_exists($sVar, $this->casts) && $this->casts[$sVar] === 'json')) {
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
				elseif($sLang && in_array($sVar, $this->aTranslateVars)){
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
						if ($this->force_lang) {
							$fLang = $this->force_lang;

							if (empty($value->$fLang)) {
								//var_dump('-- Is Empty');
								$aResult[$sVar] = null;
							}
							else{
								//var_dump('-- Getting Data');
								$aResult[$sVar] = $value->$fLang;
							}
						}
						elseif (empty($value->$sLang)) {
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
			//$aResult['sCurLang'] = $sLang;
			if ($sLang) {
				$aResult['sCurLang'] = empty($this->attributes['force_lang']) ? $sLang : $this->attributes['force_lang'];
			}
			else {
				$aResult['sCurLang'] = false;
			}
			//var_dump("-- Adding Force Lang");
		}

		//var_dump(@$this->attributes);
		$aResult['force_lang'] = empty($this->attributes['force_lang']) ? null : $this->attributes['force_lang'];

		//var_dump("-- Result: ", $aResult);

		return $aResult;
	}

	public function save(Array $options=[]) {
		$aTmpVars = [];
		//var_dump("==== SAVING ====");
		foreach ($this->attributes as $sKey => $value) {
			if (!in_array($sKey, $this->fillable)) {
				
				//var_dump("==== Skipping: $sKey ====");
				$aTmpVars[$sKey] = $value;
				unset($this->attributes[$sKey]);
				continue;
			}

			//var_dump("-- $sKey: ", $value);
			if (array_key_exists($sKey, $this->getCasts()) && $this->getCasts()[$sKey] === 'json') {
				if (!is_string($value) && !is_null($value)) {
					$this->attributes[$sKey] = json_encode($value);
				}
			}
		}

		//var_dump("-- Result: ", $this->attributes);
		$bResult = Parent::save($options);
		if (!$bResult) {
			$this->errorMsg = 'Une erreur s\'est produite lors de l\'enregistrement';
		}
		else{
			$this->errorMsg = null;
		}

		$this->attributes = array_merge($this->attributes, $aTmpVars);

		return $bResult;
	}

	public function enable($b = true) {
		if (!$b) {
			$this->attributes['state'] = false;
			return true;
		}

		//var_dump("====== TEST ENABLE ======");
		
		//var_dump("FORCE LANG", $force);
		$aErrors = [];
		$force = $this->force_lang;
		$tmpLang = $this->sCurLang;
		$this->setLang(false);
		
		foreach ($this->fillable as $key) {
			if (in_array($key, ['id', 'state', 'force_lang', 'created_at', 'updated_at'])) {
				continue;
			}
			elseif(in_array($key, $this->aOptionalFields)) {
				continue;
			}

			if (empty($this->attributes[$key])) {
				$value = null;
			}
			else{
				$value =  $this->attributes[$key];
				
			}

			//var_dump("-- key $key", $value);

			if (empty($this->$key)) {
				//var_dump("-- Fail $key Is Empty", $this->$key);
				//$this->attributes['state'] = false;
				//var_dump($this->state);
				//var_dump($this->attributes);
				$aErrors[] = $this->getProperyName($key);
				//var_dump('TEST-1: '.$key, $value);
				continue;
				//return false;
			}
			
			if (in_array($key, $this->aTranslateVars)) {
				if (is_string($value)) {
					$this->attributes[$key] = json_decode($value);
					$value = $this->attributes[$key];
				}

				if ($force) {
					if (empty($value->$force)) {
						//var_dump("-- Fail $key Is Empty For Force Lang: '$force'", $value);
						//$this->attributes['state'] = false;
						$aErrors[] = $this->getProperyName($key);
						//var_dump('TEST-2: '.$key, $value);
						continue;
						//return false;
					}
				}
				elseif(empty($value->fr) || empty($value->en)) {
					//var_dump("-- Fail $key Is Empty For One Lang", $value);
					//$this->attributes['state'] = false;
					//var_dump('TEST-3: '.$key, $value);
					$aErrors[] = $this->getProperyName($key);
					continue;
					//return false;
				}
			}
		}

		if (empty($aErrors)) {
			$this->errorMsg = null;
		}
		else{
			$this->errorMsg = 'Impossible d\'activer '.$this->userStr.'. Veuillez renseinger les champs suivants ';
			$this->errorMsg .= !$this->force_lang ? 'dans toutes les langues:' : 'dans la langue:';

			$this->errorMsg .= '<ul>';
			foreach ($aErrors as $name) {
				$this->errorMsg .= '<li>'.$name.'</li>';
			}
			$this->errorMsg .= '</ul>';

		}

		$this->attributes['state'] = empty($aErrors);
		return empty($aErrors);
	}

	public function updateData($aData) {
		$bCurState = $this->state;

		//var_dump("=== Updating Object ===", $aData);
		foreach ($aData as $key => $value) {
			//var_dump("################ $key ################");
			// Données à Ignorer lors de l'update
			if (in_array($key, ['id', 'created_at', 'updated_at', 'state'])) {
				continue;
			}
			if (in_array($key, $this->aUploads)) {
				if (empty($value)) {
					continue;
				}

				if (is_array($aData[$key])) {
					$aFiles = &$aData[$key];

					if (empty($this->$key)) {
						$this->$key = new \StdClass;
					}

					$aCurFiles = (object)$this->$key;


					foreach($aFiles as $i => $sFile) {
						if (!empty($sFile) && (empty($aCurFiles->$i) || $aCurFiles->$i !== $sFile)) {
							$this->downloadImage($sFile);
							$aCurFiles->$i = $sFile;
						}
						/*else{
							var_dump("#### Faild:");
							var_dump("-- is Empty: ", !empty($sFile));
							var_dump("-- Cur Empty: ", empty($aCurFiles->$i));
							var_dump("-- Diff: ",  $aCurFiles->$i !== $sFile);
						}*/
					}

					//var_dump("VERIFY-4: $key", $value);
					$this->$key = $aCurFiles;
					//var_dump("====== TEST Result =====", $this->$key);
				}
				else{
					//var_dump("=== $key ===");
					//var_dump("UPLOAD: $key");
					$sFile = $aData[$key];
					//var_dump("New: ". $sFile);
					//var_dump("Cur: ".$this->$key);
					//var_dump("Current: ".$this->$key);
					//var_dump("New: ".$aData[$key]);
					
					if($sFile !== $this->attributes[$key]) {
						//var_dump("Downloading");
						
						$this->downloadImage($sFile);
						$this->attributes[$key] = $sFile;

						//var_dump("Downloading DONE");
					}

					//var_dump("CHECKING: ".$this->attributes[$key]);
					continue;
				}
			}
			else if (in_array($key, $this->aTranslateVars)) {
				//var_dump("VERIFY-0: $key", $value);
				if ($this->sCurLang) {
					$this->setValueByLang($key, $value);
				}
				else{
					$this->setValueAsJson($key, $value);
				}
			}
			elseif (isset($this->casts[$key]) && $this->casts[$key] === 'json') {
				//var_dump("VERIFY-1: $key", (object)$value);
				$this->attributes[$key] = (object)$value;
			}
			else{
				//var_dump("VERIFY-2: $key", $value);
				$this->$key = $value;
			}
		}

		//var_dump("--- TEST", $this->attributes);

		//var_dump("--- Change Enable");
		//var_dump($this);
		//var_dump($this->attributes);
		if (array_key_exists('state', $aData)) {
			$b = $this->enable((bool)$aData['state']);
		}
		else{
			$b = $this->enable($this->attributes['state']);
		}

		//var_dump($this->attributes);

		if (!$b && $bCurState) {
			$this->errorMsg = str_replace(
				'd\'activer '.$this->userStr, 
				'de sauvegarder '.$this->userStr.' sans le désactiver', 
				$this->errorMsg
			);
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

		//var_dump("URL: $imgUrl");
		$b = file_put_contents($sPath, fopen($imgUrl, 'r'));
	}

	public function loadParents() {

	}

	public function getError() {
		return $this->errorMsg;
	}

	public function getProperyName($sProp) {
		return empty($this->aProperties) ? null : $this->aProperties[$sProp];
	}

	abstract public static function search($search, $columns);
}
