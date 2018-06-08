<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;

class ModelUtil extends Model
{
	public static $bAdmin = false; // Langue Séléctionnée
	private $sCurLang = false; // Langue Séléctionnée
	protected $aTranslateVars = []; // les Variables à traduire
	protected $aSkipPublic = ['created_at', 'state', 'sCurLang'];

	/**
	 * Mets à jour une variable traductible pour une langue
	 * @param String $sVar  Nom de la variable
	 * @param mixed $value valeur de la variable
	 */
	private function setValueByLang($sVar, $value) {
	    $sLang = $this->sCurLang;
	    
	    if ($value !== false && empty($value)) {
	        $value = null;
	    }
	    
	    if (empty($this->attributes[$sVar])) {
	        $var = new \StdClass;
	    }
	    else{
	        $var = $this->attributes[$sVar];
	    }

	    // Si la valeur actuelle est une string on la décode
	    if(is_string($var)) {
	        $var = json_decode($var);
	    }


	    $var->$sLang = $value;
	    
	    $this->attributes[$sVar] = $var;
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

			// Si il s'agit d'une variable à traduire
			if (in_array($sVar, $this->aTranslateVars)) {
					if (empty($this->attributes[$sVar])) {
							$var = new \StdClass;
					}
					else{
							$var = $this->attributes[$sVar];
					}

					// Si la valeur actuelle est une string on la décode
					if (is_string($var)) {
							$var = json_decode($var);

							// En cas d'échec on retourne NULL
							if (is_null($var)) {
									return null;
							}

							$this->attributes[$sVar] = $var;
					}

					// Si une langue est séléctionnée
					if ($this->sCurLang) {
							$sLang = $this->sCurLang;
							return empty($var->$sLang) ? null : $var->$sLang;
					}

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
	    // Si il s'agit d'une variable à traduire
	    if (in_array($sVar, $this->aTranslateVars)) {
	        
	        $var = $this->$sVar;
	        
	        // Si une langue est choisie on met à jour que celle ci
	        if ($this->sCurLang) {
	            $this->setValueByLang($sVar, $value);
	        }
	        // Si aucune langue est choisie on les met toutes à jour
	        else{
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

	    foreach ($this->attributes as $sVar => $value) {

	        if (in_array($sVar, $this->hidden) || ((!$bAdmin && !static::$bAdmin) && in_array($sVar, $this->aSkipPublic))) {
	            continue;
	        }

	        if (array_key_exists($sVar, $this->getCasts()) && $this->getCasts()[$sVar] === 'json') {
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
	                $aGeo = explode(';', $value);
	                $aResult['geo'] = [
	                    'latitude' => empty($aGeo[0]) ? 0 : $aGeo[0],
	                    'longitude' => empty($aGeo[1]) ? 0 : $aGeo[1]
	                ];
	            }
	            
	            $aResult[$sVar] = $value;
	        }
	    }

	    if ($bAdmin || static::$bAdmin) {
	    	$aResult['sCurLang'] = $sLang;
	    	$aResult['force_lang'] = empty($this->attributes['force_lang']) ? null : $this->attributes['force_lang'];
	    }

	    return $aResult;
	}

	public function save(Array $options=[]) {
		foreach ($this->attributes as $skey => $iAttr) {
		 	if (
		 		in_array($skey, $this->aTranslateVars) ||
		 		(array_key_exists($skey, $this->getCasts()) && 
	 		 		$this->getCasts()[$skey] === 'json'
	 		 	)
		 	) {
				$this->attributes[$skey] = json_encode($iAttr);
			}
		}

	    return Parent::save($options);
	}
}
