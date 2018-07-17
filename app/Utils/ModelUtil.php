<?php

namespace App\Utils;

use App\Utils\RequestUtil as Request;
use Illuminate\Database\Eloquent\Model;

abstract class ModelUtil extends Model
{
	/**
	 * Langue Séléctionnée
	 */
	public $sCurLang = false;

	/**
	 * Nom du Model pour un utilisateur
	 */
	protected $userStr = 'le Model';

	/**
	 * Table Enfant. Utilisé pour le Context public
	 */
	protected static $sChildTable = false;
	
	/**
	 * les Variables à traduire
	 */
	protected $aTranslateVars = [];

	/**
	 * Variable à masquer pour le Context Public
	 */
	protected $aSkipPublic = ['created_at', 'state', 'sCurLang'];
	
	/**
	 * Variables à Correspondant à un Upload
	 */
	protected $aUploads = ['image', 'audio'];

	/**
	 * Liste Des traduction des propriétés
	 */
	protected $aProperties = [];

	/**
	 * Message D'erreur
	 */
	protected $errorMsg = null;

	/**
	 * Champs Optionnels pour l'Activation
	 */
	protected $aOptionalFields = [];

	public function getId() {
		return empty($this->attributes['id']) ? null : $this->attributes['id'];
	}

	/**
	 * Type de Collection du Model
	 * @param  array  $models Liste des Models
	 * @return TransactionCollection         Collection
	 */
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
	 * Réécriture Logique empty()
	 * @param  String  $sVar Nom de la variable
	 * @return boolean       La variable sest vide
	 */
	public function __isset($sVar) {
		// Si la variable ne fait pas parti de la table BDD
		if (!in_array($sVar, $this->fillable) && !array_key_exists($sVar, $this->attributes)) {
			return Parent::__isset($sVar);
		}
		
		// Si la Variable est de type Json, on le décode
		if (isset($this->casts[$sVar]) && $this->casts[$sVar] === 'json' && is_string(@$this->attributes[$sVar])) {
			$this->attributes[$sVar] = json_decode($this->attributes[$sVar]);
		}

		// Si la Variable est un Json (StdClass)
		if (is_object(@$this->attributes[$sVar])) {
			return !empty(get_object_vars($this->attributes[$sVar]));
		}
		else{
			return !empty($this->attributes[$sVar]);
		}
	}

	/**
	 * Réécritue Récupération de variable
	 * @param  String $sVar Nom de la variable
	 * @return Mixed       La variable ou NULL
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

	/**
	 * Réécritue Ecriture de variable
	 * @param String $sVar  Nom de la variable
	 * @param Mixed $value Valeur de la variable 
	 */
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
		// Si la variable fait parti de la table BDD
		elseif(array_key_exists($sVar, $this->attributes) || in_array($sVar, $this->fillable)){

			// Si la Variable est de type Json, on le Cast en StdClass
			if (isset($this->casts[$sVar]) && $this->casts[$sVar] === 'json' && !is_object($value)) {
				if (is_string($value)) {
					$value = json_decode($value);
				}
				elseif(is_array($value)) {
					$value = (object)$value;
				}
			}
			$this->attributes[$sVar] = $value;
		}
		// Si la variable Fait parti de l'Objet
		elseif(property_exists($this, $sVar)){
			$this->$sVar = $value;
		}
		else{
			throw new \Exception("Error: Try To Set $sVar In Model", 1);
		}
	}

	
	// Définis la langue de l'objet
	public function setLang($l = false) {
		// Si la Langue est Différent De défault
		if ($l !== 'default') {
			if(strlen($this->force_lang)){
				$this->sCurLang = $this->force_lang;
			}
			else{
				$this->sCurLang = $l;
			}
		}
		// Si le Force Lang Est défini
		elseif(strlen($this->force_lang)){
			$this->sCurLang = $this->force_lang;
		}
		// Par Défaut La Langue Est Français
		else {
			$this->sCurLang = 'fr';
		}
	}

	// Définis la langue de l'objet
	public function getLang() {
		return $this->sCurLang;
	}

	/**
	 * Retrourne une nouvelle instance vide
	 * @return Cities nouvelle instance
	 */
	public static function getInstance() {
		return null;
	}

	/**
	 * Prépare la requete pour lister le Model
	 * @param  Request $oRequest Requete
	 * @param  boolean $bAdmin  Retourne Le QueryNuilder Au lieu du Résultat De Requete
	 * @return QueryBuilder            Le Constructeur De Requete SQL
	 */
	public static function list(Request $oRequest, $bAdmin=false) {
		$skip = $oRequest->getSkip();
		$limit = $oRequest->getLimit();
		$sLang = $oRequest->input('lang');
		$aOrder = $oRequest->getOrder();
		$oModel = static::getInstance();

		$sTable = $oModel->table;
		$oModelList = Self::Select($sTable.'.*');


		// Si le Context est public
		if (!$bAdmin) {
			// Si une langue est sélectionnée
			if ($sLang) {
				var_dump($sLang);
				// On Cible la langue
			    $oModelList->where(function($query) use ($sLang, $bAdmin, $sTable){
		            $query->Where($sTable.'.force_lang', '')
		            	->orWhereNull($sTable.'.force_lang')
		            	->orWhere($sTable.'.force_lang', $sLang);
			    });
			}

			// Si le Model à un Model Enfant On ne retourne les resultats qui ont au moins 1 enfant
			if (static::$sChildTable) {
				$sChild = static::$sChildTable;
				
				$childColumn = static::$sChildTable.'.';
				$childColumn .= $oModel->table;
				$childColumn .= '_id';

				// Jointure de la table Enfant
				$oModelList->leftJoin($sChild, function($query) use ($childColumn, $sChild, $sTable, $sLang) {
					$query->on($sTable.'.id', '=', $childColumn);

					if ($sLang) {
						$query->Where(function($query) use ($sChild, $sLang) {
							$query->Where($sChild.'.force_lang', '')
								  ->orWhereNull($sChild.'.force_lang')
							;

							$query->orWhere($sChild.'.force_lang', $sLang);
						});
					}
				});

				$oModelList->groupBy($sTable.'.id');
		    	$oModelList->whereNotNull($sChild.'.id');
			}

			// On Force le Status Actif
		    $oModelList->where($sTable.'.state', true);
		}

		// Si un order est demandé
		if ($aOrder && count($aOrder) === 2) {
		    $aOrderCol = $aOrder[0];
		    $sOrderWay = $aOrder[1];

		    // Si l'ordre Correspond à plusieurs colones 
		    if (!is_array($aOrderCol)) {
		        $aOrderCol = [$aOrderCol];
		    }

		    // Réécriture du nom de la colone
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

		        //var_dump($aOrder, $sOrderCol, $sOrderWay);
		    	// Order By la colone
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

		// Pour chacune des propriétés
		foreach ($this->attributes as $sVar => $value) {
			//var_dump("+++++++++ Var: $sVar", $value);
			
			// Si ignore les variables cachées au Contexte Public
			if (in_array($sVar, $this->hidden) || (!$bAdmin && in_array($sVar, $this->aSkipPublic))) {
				//var_dump("-- Hidden Skip");
				continue;
			}

			// Si la valeur est un Model On le transforme en Array
			if ($value instanceOf ModelUtil) {
				$aResult[$sVar] = $value->toArray($bAdmin);
				continue;
			}

			// Si la variable est de Type Json
			if (in_array($sVar, $this->aTranslateVars) || (array_key_exists($sVar, $this->casts) && $this->casts[$sVar] === 'json')) {
				//var_dump("-- Translate Var");
				// On Décode le Json
				if (is_string($value)) {
					//var_dump("-- Decoding");
					$value = json_decode($value);
				}
				//var_dump($value);

				// Si la valeur est Vide initialise
				if (empty($value)) {
					//var_dump('-- Is Empty');
					$value = new \StdClass;
				}
				// Si une langue est séléctionnée
				elseif($sLang && in_array($sVar, $this->aTranslateVars)){
					//var_dump("-- Selecting Lang $sLang");
					
					// Si le Context est Public
					if (!$bAdmin) {
						//var_dump('-- Not Admin');
						// On Force le format Json
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
						// Si Un langue est forcée On récupère celle ci
						$l = $this->force_lang ? $this->force_lang : $sLang;

						if (empty($value->$l)) {
							//var_dump('-- Is Empty');
							$aResult[$sVar] = null;
						}
						else{
							//var_dump('-- Getting Data');
							$aResult[$sVar] = $value->$l;
						}
					}
					
				}
				else{
					//var_dump('-- Getting Full Data');
					$aResult[$sVar] = $value;
				}
			}
			else{
				// Si la variable Est une Géolocalisation
				if ($sVar === "geoloc") {
					// On Décode le json
					if (is_string($value)) {
						$value = json_decode($value);
					}

					// var_dump($value);
					//$aGeo = explode(';', $value);
					// Si le Context Est Public
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
		}

		// Si Le Context Est Privé
		if ($bAdmin) {
			//var_dump("-- Adding Lang");
			//$aResult['sCurLang'] = $sLang;
			
			// Si Une langue est On précise laquelle est utilisée
			if ($sLang) {
				$aResult['sCurLang'] = empty($this->attributes['force_lang']) ? $sLang : $this->attributes['force_lang'];
			}
			else {
				$aResult['sCurLang'] = false;
			}
		}

		//var_dump(@$this->attributes);
		// On précise si la langue est forcée
		$aResult['force_lang'] = empty($this->attributes['force_lang']) ? null : $this->attributes['force_lang'];

		//var_dump("-- Result: ", $aResult);

		return $aResult;
	}

	/**
	 * Insert / Update Model
	 * @param  Array|array $options Options Lumen
	 * @return Bool               Success
	 */
	public function save(Array $options=[]) {
		// var_dump("Model SAVING");
		// Cache de Variables
		$aTmpVars = [];
		//var_dump("==== SAVING ====");
		
		// Pour Chaques Varialbes
		foreach ($this->attributes as $sKey => $value) {

			// Si la variable ne fait pas parti de la table BDD On la met de coté
			if (!in_array($sKey, $this->fillable)) {
				
				//var_dump("==== Skipping: $sKey ====");
				$aTmpVars[$sKey] = $value;
				unset($this->attributes[$sKey]);
				continue;
			}

			// Si la variable est de type Json
			if (array_key_exists($sKey, $this->getCasts()) && $this->getCasts()[$sKey] === 'json') {

				// On la Décode
				if (!is_string($value) && !is_null($value)) {
					$this->attributes[$sKey] = json_encode($value);
				}
			}
		}

		// Application de la Sauvegarde
		$bResult = Parent::save($options);
		if (!$bResult) {
			$this->errorMsg = 'Une erreur s\'est produite lors de l\'enregistrement';
		}
		else{
			$this->errorMsg = null;
		}

		// On  remet en place les Variables ignorées
		$this->attributes = array_merge($this->attributes, $aTmpVars);

		return $bResult;
	}

	/**
	 * Activation Du Model
	 * @param  boolean $b Activer ou Non
	 * @return Bool     Success
	 */
	public function enable($b = true) {
		
		// Si la demande est une Dé-activation Pas de vérification necessaires
		if (!$b) {
			$this->attributes['state'] = false;
			return true;
		}

		//var_dump("====== TEST ENABLE ======");
		
		// Liste des Champs Erronés
		$aErrors = [];

		// Langue Forcée
		$force = $this->force_lang;

		// On garde la langue actuelle
		$tmpLang = $this->sCurLang;

		// On Dé-Séléctionne la langue pour Obtenir toutes les Infos
		$this->setLang(false);
		//var_dump("FORCE LANG", $force);
		
		// Pour Chaques champs modifiable
		foreach ($this->fillable as $key) {
			// Champs à ignorer
			if (in_array($key, ['id', 'state', 'force_lang', 'created_at', 'updated_at'])) {
				continue;
			}
			// Si le Champs est optionel
			elseif(in_array($key, $this->aOptionalFields)) {
				continue;
			}

			// Récupération de la valeur
			if (empty($this->attributes[$key])) {
				$value = null;
			}
			else{
				$value =  $this->attributes[$key];
				
			}

			//var_dump("-- key $key", $value);

			// Si La valeur est vide On ne peut pas activer  le Model
			if (empty($this->$key)) {
				//var_dump('isEmpty');
				//var_dump("-- Fail $key Is Empty", $this->$key);
				$aErrors[] = $this->getProperyName($key);
				continue;
			}
			
			// Si la Variable est à traduire
			if (in_array($key, $this->aTranslateVars)) {
				//var_dump("Tanslate");
				
				// On Décode le Json
				if (is_string($value)) {
					$this->attributes[$key] = json_decode($value);
					$value = $this->attributes[$key];
				}

				//var_dump($value);

				// Si une langue est Forcé
				if ($force) {
					// On Vérifie Seulement Cette langue
					if (empty($value->$force)) {
						//var_dump("force Empty");
						//var_dump("-- Fail $key Is Empty For Force Lang: '$force'", $value);
						$aErrors[] = $this->getProperyName($key);
						continue;
					}
				}
				// On Vérifie Seulement toutes les langues
				elseif(empty($value->fr) || empty($value->en)) {
					//var_dump("Langs Empty");
					//var_dump("-- Fail $key Is Empty For One Lang", $value);
					$aErrors[] = $this->getProperyName($key);
					continue;
				}
			}
		}

		// var_dump("Model Update: ", $aErrors);
		// Si Aucune Erreur On reset le Message
		if (empty($aErrors)) {
			$this->errorMsg = null;
		}
		// Si Erreur Ecriture du Message
		else{
			$this->errorMsg = 'Impossible d\'activer '.$this->userStr.'.<br>Veuillez renseinger les champs suivants ';
			$this->errorMsg .= !$this->force_lang ? 'dans <b>toutes les langues</b> :' : 'dans la langue :';

			$this->errorMsg .= '<ul>';
			foreach ($aErrors as $name) {
				$this->errorMsg .= '<li>'.$name.'</li>';
			}
			$this->errorMsg .= '</ul>';

		}

		// Définition du status
		$this->attributes['state'] = empty($aErrors);
		return empty($aErrors);
	}

	/**
	 * Mets à Jour le Model
	 * @param  Array $aData Donées à Modifier
	 */
	public function updateData($aData) {
		//var_dump("Model Update");
		
		// On Garde En Mémoire le Status Courrant
		$bCurState = (bool) @$this->attributes['state'];

		//var_dump("=== Updating Object ===", $aData);
		// Mise à Jour des Donnés
		foreach ($aData as $key => $value) {
			//var_dump("################ $key ################", $value);
			
			// Données à Ignorer lors de l'update
			if (in_array($key, ['id', 'created_at', 'updated_at', 'state'])) {
				continue;
			}
			// Si la varialbe représente un fichier
			if (in_array($key, $this->aUploads)) {
				//var_dump($key, $value);
				// Si Aucune image On Ignore
				if (empty($value)) {
					//var_dump("Empty");
					continue;
				}

				// Si la variable est un tableau de fichiers
				if (is_array($aData[$key])) {
					$aFiles = &$aData[$key];

					// Si la Variables est Vide on l'initialise
					if (empty($this->$key)) {
						$this->$key = new \StdClass;
					}

					// Les Fichiers Actuels
					$aCurFiles = (object)$this->$key;

					foreach($aFiles as $i => $sFile) {
						// Si le fichier n'est pas vide et est différent du fichier actuel On le Télécharge
						if (!empty($sFile) && (empty($aCurFiles->$i) || $aCurFiles->$i !== $sFile)) {
							$this->downloadImage($sFile);
							$aCurFiles->$i = $sFile;
						}
					}

					//var_dump("VERIFY-4: $key", $value);
					
					// Mise à jour de la liste des fichiers
					$this->$key = $aCurFiles;
					//var_dump("====== TEST Result =====", $this->$key);
				}
				else{
					// Le Fichier Actuel
					$sFile = $aData[$key];

					// Si le fichier est différent du fichier actuel On le Télécharge
					if($sFile !== @$this->$key) {
						//var_dump("Downloading");
						
						$this->downloadImage($sFile);
						if (!in_array($key, $this->aTranslateVars)) {
							$this->attributes[$key] = $sFile;
						}
						else{
							$this->setValueByLang($key, $value);
						}

						//var_dump("Downloading DONE");
					}

					//var_dump("CHECKING: ",$this->attributes[$key]);
					continue;
				}
			}
			// Si la Variable est à traduire
			else if (in_array($key, $this->aTranslateVars)) {
				//var_dump("VERIFY-0: $key", $value);
				// Définition de la langue même
				if ($this->sCurLang) {
					$this->setValueByLang($key, $value);
				}
				// Définition de toutes les langues
				else{
					$this->setValueAsJson($key, $value);
				}
			}
			// Si la Variable est un Json On Cast l'array en StdClass
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
		//var_dump("--- Model Change Enable");
		
		// Si la Mise à jour du status est demandée
		if (array_key_exists('state', $aData)) {
			$b = $this->enable((bool)$aData['state']);
		}
		// Si Non on Vérifie que son status est valide
		else{
			$b = $this->enable($this->attributes['state']);
		}

		// Si La mise à jour du status a échoué et que le Model est Actuellement Actif
		if (!$b && $bCurState) {

			// On Précise qu'on Ne Peut Pas Sauvegarder Sans Dé-Activer Le Model
			$this->errorMsg = str_replace(
				'd\'activer '.$this->userStr, 
				'de sauvegarder '.$this->userStr.' sans le désactiver', 
				$this->errorMsg
			);
		}
	}

	/**
	 * Téléchargement du Fichier
	 * @param  String $sName Nom de l'image
	 * @return Int        Nombre d'Octés Ecrits
	 */
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
		return $b;
	}

	/**
	 * Récupère les Modèles Parents
	 */
	public function loadParents() {

	}

	/**
	 * Récupère le Message d'Erreur
	 */
	public function getError() {
		return $this->errorMsg;
	}

	/**
	 * Retourne le nom traduit de laVariable
	 * @param  String $sProp le Nom de la variable
	 * @return String        la Traduction ou NULL
	 */
	public function getProperyName($sProp) {
		return empty($this->aProperties) ? null : $this->aProperties[$sProp];
	}

	/**
	 * Recherche une phrase dans tous les Points
	 * @param  String  $query   Recherche
	 * @param  Array  $columns  Colones à retoruner
	 * @param  Array  $order    Ordre de retour
	 * @return TranslateCollection Collection des Modeles
	 */
	abstract public static function search($search, $columns, $order = false);
}
