<?php
namespace App\Models;

use App\Utils\ModelUtil;

class Interests extends ModelUtil
{
	protected $table = 'interests';
	protected $userStr = 'le point d\'intérêt';	// Nom du Model pour un utilisateur
	
	protected $parcours = false;	// Parcours Associé
	protected $distances = false;	// Les Différent Trajets Associes
	protected $api_response = false;	// La réponse de Open Route Service pour l'affichage des la map

	public $way = true;
	public $timestamps = true;

	/**
	 * Liste Des traduction des propriétés
	 */
	protected $aProperties = [
		'title' => 'Titre',
		'state' => 'Status',
		'geoloc' => 'Géolocalisation',
		'header_image' => 'Image de Couverture',
		'gallery_image' => 'Gallerie d\'image',
		'cities_id' => 'Ville',
		'parcours_id' => 'Parcours',
		'address' => 'Accroche',
		'schedule' => 'Horaires',
		'price' => 'Difficulté',
		'audio' => 'Audio',
		'transport' => 'Transports',
		'audio_script' => 'Script Audio',
		'description' => 'Déscription',
		'bibliography' => 'Bibliographie',
	];

	/**
	 * Varialbles modifialbes en DB
	 */
	protected $fillable = [
		'id',
		'cities_id',
		'parcours_id',
		'header_image',
		'title',
		'address',
		'geoloc',
		'schedule',
		'price',
		'audio',
		'transport',
		'audio_script',
		'description',
		'gallery_image',
		'bibliography',
		'force_lang',
		'state',
		'created_at',
		'updated_at'
	];

	/**
	 * Types des colones particulières 
	 */
	protected $casts = [
		'id' => 'string',
		'cities_id' => 'string',
		'parcours_id' => 'string',
		'title' => 'json',
		'address' => 'json',
		'geoloc' => 'json',
		'schedule' => 'json',
		'price' => 'json',
		'audio' => 'json',
		'transport' => 'json',
		'audio_script' => 'json',
		'description' => 'json',
		'gallery_image' => 'json',
		'bibliography' => 'json',
	];

	protected $primaryKey = 'id';

	/**
	 * Variables à Traduire
	 */
	protected $aTranslateVars = [
		'title', 
		'address', 
		'schedule', 
		'price', 
		'audio', 
		'transport',
		'audio_script',
		'description',
		'bibliography',
	];

	/**
	 * Variables à Correspondant à un Upload
	 */
	protected $aUploads = ['header_image', 'audio', 'gallery_image'];
	
	/**
	 * Champs Optionnels pour l'Activation
	 */
	protected $aOptionalFields = ['parcours_id'];

	/**
	 * Réécritue Récupération de variable
	 * @param  String $sVar Nom de la variable
	 * @return Mixed       La variable ou NULL
	 */
	public function __get($sVar) {
		switch ($sVar) {
			case 'distances':
				return $this->getDistances();
				break;
		}

		return Parent::__get($sVar);
	}

	/**
	 * Retourne la liste des Trajets Liés  Au Point
	 * @return Array Liste De InterestWay
	 */
	public function getDistances() {
		if (is_array($this->distances)) {
			return $this->distances;
		}
		
		$attrs = &$this->attributes;
		if (empty($attrs['parcours_id'])) {
			$this->distances = [];
			return $this->distances;
		}

		$oWayList = InterestWay::byInterest($this);
		$this->distances = [];

		foreach ($oWayList as $oWay) {
			$intID = null;
			if ($oWay->int1 === $this->id) {
				$intID = $oWay->int2;
			}
			else{
				$intID = $oWay->int1;
			}
			
			$this->distances[$intID] = [
				'time' => $oWay->time,
				'distance' => $oWay->distance
			];
		}

		return $this->distances;
	}

	/**
	 * Retrourne une nouvelle instance vide
	 * @return Parcours nouvelle instance
	 */
	public static function getInstance() {
		return new Interests;
	}

	/**
	 * Récupère La ville Associée
	 * @return Cities Model Associé
	 */
	public function loadCity() {
		if (!empty($this->attributes['cities_id'])) {
			$city = Cities::Where('id', $this->attributes['cities_id'])->get(['id', 'title'])->first();

			if (!empty($city)) {				
				$city->setLang($this->getLang());
				$this->attributes['city'] = $city;
			}
		}

		return $this->attributes['city'];
	}


	/**
	 * Récupère Le Parcours Associée
	 * @return Parcours Model Associé
	 */
	public function loadParcours() {
		if (!empty($this->attributes['parcours_id'])) {
			$parcours = Parcours::Where('id', $this->attributes['parcours_id'])->get(['id', 'title'])->first();

			if (!empty($parcours)) {
				$parcours->setLang($this->getLang());
				$this->attributes['parcours'] = $parcours;
			}
			else{
				$this->attributes['parcours'] = null;
			}
		}
		elseif(!array_key_exists('parcours', $this->attributes)) {
			$this->attributes['parcours'] = null;
		}

		return $this->attributes['parcours'];
	}

	/**
	 * Récupère le Parcours Et La ville
	 */
	public function loadParents() {
		$this->loadCity();
		$this->loadParcours();
	}

	/**
	 * Mets à Jour le Model
	 * @param  Array $aData Donées à Modifier
	 */
	public function updateData($aData) {
		if (!empty($aData['geoloc']) && (object)$aData['geoloc'] !== $this->geoloc) {
			InterestWay::deleteByInterest($this, true);
		}

		Parent::updateData($aData);
	}

	/**
	 * Insert / Update Model
	 * @param  Array|array $options Options Lumen
	 * @return Bool               Success
	 */
	public function save(Array $options=[]) {
		// Récupération Du Status Actuel
		$curState = (bool)@$this->original['state'];

		// Récupération De la Géoloc Actuel
		$prevGeo = empty($this->original['geoloc']) ? false : $this->original['geoloc'];
		// Récupération Du Parcours Actuel
		$prevParc = empty($this->original['parcours_id']) ? false : $this->original['parcours_id'];


		// Liste des Attributs
		$attrs = &$this->attributes;

		// Géoloc Identique
		$bGeoSame = $attrs['geoloc'] === $prevGeo;
		// Parcours Mit à Jour
		$bParcoursUpdated = $prevParc === @$attrs['parcours_id'];

		// SI le parcours et la geoloc Sont les Mêmes, Pas la peine de Mettre à jour les Trajets
		if ($bGeoSame && !$bParcoursUpdated) {
			return Parent::save($options);
		}
		// SI le Point N'existe pas On L'insère
		elseif(empty($this->attributes['id'])){
			$b = Parent::save($options);
			if (!$b) {
				return false;
			}
		}
		// Suppression Des Trajet Actuels
		else{
			InterestWay::deleteByInterest($this, true);
		}

		// Si Aucune Geoloc Ou Aucun Parcours On ne peut pas calculer de Trajet, On Sauvegarde
		if (empty($this->geoloc) || empty($attrs['parcours_id'])) {
			$this->distances = [];
			$this->parcours = null;
			return Parent::save($options);
		}
	
		// Mise à Jour des Trajets
		$this->distances = InterestWay::updateByInterest($this);

		// Libération De Mémoire
		$this->parcours = null;

		// Init Message D'erreur
		$msg = null;

		// Si Le Point est Actif
		if ($this->attributes['state']) {
			
			// On Vérifie Que L'activation Est Possible
			$b = $this->enable(true);

			// Si L'activation A echoué et Que Le Point Est Actif
			// On Refuse la mise à jour
			if (!$b && $curState) {
				// Modification du Msg D'erreur
				$this->errorMsg = str_replace(
					'd\'activer '.$this->userStr, 
					'de sauvegarder '.$this->userStr.' sans le désactiver', 
					$this->errorMsg
				);

				// Suppression Des Nouveaux Trajets
				InterestWay::deleteByInterest($this);
				// Re Mise en place Des Trajets Précédemment Supprimés
				InterestWay::restoreDeleted();
				return false;
			}
			// Si L'activation A echoué On Garde Le Message D'Erreur de Coté
			elseif (!$b) {
				$msg = $this->errorMsg;
			}
		}

		// On Sauvegarde Les Données
		$b = Parent::save($options);

		// Si L'enregistrement Est OK On remet en place le potentiel Message d'erreur
		if ($b) {
			$this->errorMsg = $msg;
		}
		// Si L'enregistrement à Echouer On Remets En Place les Trajets
		else{
			// Suppression Des Nouveaux Trajets
			InterestWay::deleteByInterest($this);
			// Re Mise en place Des Trajets Précédemment Supprimés
			InterestWay::restoreDeleted();
		}

		return $b;
	}

	/**
	 * Suppression Du Model
	 * @return Bool Success
	 */
	public function delete() {
		if (empty($this->attributes['parcours_id'])) {
			return Parent::delete();
		}

		// Si Un Parcoures Est asocié On Supprime les Trajets
		if (!empty($this->attributes['parcours_id'])) {
			$b = InterestWay::deleteByInterest($this);
			if (!$b) {
				return false;
			}
		}

		$b = Parent::delete();
		return $b;
	}

	/**
	 * Activation Du Model
	 * @param  boolean $b Activer ou Non
	 * @return Bool     Success
	 */
	public function enable($b = true) {
		// Activation Parente
		$success = Parent::enable($b);
		if (!$success) {
			return $success;
		}

		// RÉCUPÉRATION DES TRAJETS
		$oWayList = InterestWay::byInterest($this);
		$aErrors = [];

		// On RECHERCHE LES TRAJETS IMPOSSIBLES
		foreach ($oWayList as &$oWay) {
			// Trajet OK ON PASSE
			if ($oWay->time >= 0) {
				continue;
			}

			// RECUPERATION DU POINT QUI PAUSE PROBLEME
			$otherId = $oWay->int1 === $this->id ? $oWay->int2 : $oWay->int1;
			$oInt = Interests::Where([['id', '=', $otherId], ['state', '=', true]])->get(['title'])->first();
			if (!$oInt) {
				continue;
			}

			// Ajout Du TITRE A LA LISTE DES POINTS QUI PAUSENT PROBLEME
			$oInt->setLang('fr');
			$aErrors[] = $oInt->title;
		}

		// Si ON N A PAS D ERREUR ON Ecrase Le Message Et On RETOUNE
		if (empty($aErrors)) {
			$this->errorMsg = null;
			return true;
		}

		// GENERATION DU MSG D ERREUR
		$this->errorMsg = 'Impossible d\'activer '.$this->userStr.'.<br>Il semblerait que le trajet à pied est impossible avec les points suivants: ';

		$this->errorMsg .= '<ul>';
		foreach ($aErrors as $name) {
			$this->errorMsg .= '<li>'.$name.'</li>';
		}
		$this->errorMsg .= '</ul>';

		$this->attributes['state'] = false;
		return false;
	}

	public function toArray($bAdmin=false) {
		$aResult = Parent::toArray($bAdmin);
		if ($this->api_response) {
			$aResult['api_data'] = $this->api_response;
		}

		return $aResult;
	}

	/**
	 * Récupération Par Parcours
	 * @param  String  $id       ID Parcours
	 * @param  RequestUtil  $oRequest Requete
	 * @param  boolean $bAdmin   Context Est Admin
	 * @return TranslateCollection            Collection Des Modèles
	 */
	public static function byParcours($id, $oRequest, $bAdmin = false) {
		$where = [['parcours_id', '=', $id]];
		if (!$bAdmin) {
			$where[] = ['state', '=', 'true'];
		}

		$oModelList = Self::list($oRequest, $bAdmin);
		$oModelList->where($where);

		return $oModelList;
	}

	/**
	 * Recherche une phrase dans tous les Points
	 * @param  String  $query   Recherche
	 * @param  Array  $columns  Colones à retoruner
	 * @param  Array  $order    Ordre de retour
	 * @return TranslateCollection Collection des Modeles
	 */
	public static function search($query, $columns, $order = false) {
		// ECHAPPEMENT DES QUOTES
		$query = preg_replace("/('{1})/", ("''"), $query);
		$oModel = new Interests;

		if (!empty($columns)) {
			foreach ($columns as &$col) {
				$col = 'interests.'.$col;
			}
		}
		else{
			$columns = 'interests.*';
		}

		$list = Interests::Select($columns)
			->leftJoin('cities', 'interests.cities_id', '=', 'cities.id')
			->leftJoin('parcours', 'interests.parcours_id', '=', 'parcours.id')

			->whereRaw(	// Recherche dans le Tite
				"CONCAT(interests.title->>'fr', interests.title->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(	// Recherche dans l'adresse
				"CONCAT(interests.address->>'fr', interests.address->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(	// Recherche dans l'audio Script'
				"CONCAT(interests.audio_script->>'fr', interests.audio_script->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(	// Recherche dans la Description
				"CONCAT(interests.description->>'fr', interests.description->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(	// Recherche dans la Description
				"CONCAT(interests.bibliography->>'fr', interests.bibliography->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(	// Recherche dans le Tite Du Parcours
				"CONCAT(parcours.title->>'fr', parcours.title->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(	// Recherche dans la Description Du Parcours
				"CONCAT(parcours.description->>'fr', parcours.description->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(	// Recherche dans le Tite De la Ville
				"CONCAT(cities.title->>'fr', cities.title->>'en') ILIKE '%{$query}%'"
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
			$list->orderBy('interests.title->fr', 'ASC');
		}
		// Tri Par Score
		elseif ($orderCol === 'score') {
			$list->orderByRaw("
				".	// Recherche dans le Tite => 25 Points
				"
				((
					CONCAT(interests.title->>'fr', interests.title->>'en') ILIKE '%".$query."%')::int * 25
				) +
				".	// Recherche dans l'addresse' => 20 Points
				"
				((
					CONCAT(interests.address->>'fr', interests.address->>'en') ILIKE '%".$query."%')::int * 20
				) +
				".	// Recherche dans l'audio_script' => 15 Points
				"
				((
					CONCAT(interests.audio_script->>'fr', interests.audio_script->>'en') ILIKE '%".$query."%')::int * 15
				) +
				".	// Recherche dans la description => 13 Points
				"
				((
					CONCAT(interests.description->>'fr', interests.description->>'en') ILIKE '%".$query."%')::int * 13
				) +
				".	// Recherche dans la Bibliography => 11 Points
				"
				((
					CONCAT(interests.bibliography->>'fr', interests.bibliography->>'en') ILIKE '%".$query."%')::int * 11
				) +
				".	// Recherche dans le Titre du Parcours => 10 Points
				"
				((
					CONCAT(parcours.title->>'fr', parcours.title->>'en') ILIKE '%".$query."%')::int * 10
				) +
				".	// Recherche dans la description Du Parcours => 5 Points
				"
				((
					CONCAT(parcours.description->>'fr', parcours.description->>'en') ILIKE '%".$query."%')::int * 5
				) +
				".	// Recherche dans le Titre De La Ville => 3 Points
				"
				((
					CONCAT(cities.title->>'fr', cities.title->>'en') ILIKE '%".$query."%')::int * 3
				)

				".$orderWay."
			");
		}
		// Tris par Colone demandée
		else {
			if (!in_array($orderCol, $oModel->aTranslateVars)) {
				$list->orderBy('interests.'.$orderCol, $orderWay);
			}
			else{
				$list->orderBy('interests.'.$orderCol.'->fr', $orderWay);
			}
		}

		return $list->get();
	}

	/**
	 * Récupère le point d'interest le plus Proche
	 * @param  Array  $aGeo  tableau Latitude / longitude
	 * @param  String $sParc ID Parcours Si Demandé (FALSE => peu importe le Parcours || 'null' => Hors Parcours Uniquement) 
	 * @return Interests         Le Point Le Plus Proche de la Géoloc
	 */
	public static function closest($aGeo, $sParc=false, $sCity=false, $sLang=false, $columns=null) {
		$lat = (float) $aGeo[0];
		$lon = (float) $aGeo[1];

		$oModelList = Self::Where('state', 'true');

		// Application Du Parcours
		if ($sParc) {
			if ($sParc !== 'null') {
				$oModelList->where('parcours_id', $sParc);
			}
			else{
				$oModelList->whereNull('parcours_id');
			}
		}

		if ($sLang) {
			$oModelList->where(function($query) use ($sLang) {
				$query->whereNull('force_lang')
					  ->orWhere('force_lang', $sLang)
					  ->orWhere('force_lang', '')
				;
			});
		}

		if ($sCity) {
			$oModelList->where(function($query) use ($sCity) {
				$query->where('cities_id', $sCity);
			});
		}

		// Trie PAr Proximité
		$oModelList->orderByRaw("
			ABS((geoloc->>'latitude')::FLOAT - ".$lat.") + ABS((geoloc->>'longitude')::FLOAT - ".$lon.")
			ASC
		");


		return $oModelList->get($columns)->first();
	}

	public static function optimizeOrder(Interests $oFirst, $sLang=false, $columns=false, $bAdmin=false) {
		$aOrder = [];
		$aResults = [];
		$aPrevious = [];
		$dBestDistance = -1;

		// On Test plusieurs Trajets possible pour optimiser la parcours
		for ($i=0; $i < 3; $i++) {
			$oCurrent = $oFirst;

			$aPrevious[$i] = [];
			$aResults[$i] = [
				'time' => 0, 
				'distance' => 0,
				'interests' => [$oFirst]
			];

			if ($i > 0) {
				if (isset($aPrevious[0][1])) {
					$aPrevious[$i][0] = $aPrevious[0][1];
				}

				if ($i > 1) {
					if (isset($aPrevious[0][2])) {
						$aPrevious[$i][1] = $aPrevious[0][2];
					}
				}
			}

			$z = 0;
			$totTime = 0;
			$totDistance = 0;
			while(!is_null($oNext = InterestWay::closest($oCurrent, $aPrevious[$i], $sLang, true, $columns))) {// Récupération Du point le plus proche

				if ($i > 0 && $z == 0) {
					unset($aPrevious[$i][0]);
					if ($i > 1) {
						unset($aPrevious[$i][1]);
					}
				}

				// Si Désactivé, on continue
				if (!$oNext->state) {
					$aPrevious[$i][] = $oNext->id;
					continue;
				}

				$totTime += $oNext->way->time;
				$totDistance += $oNext->way->distance;


				$aPrevious[$i][] = $oCurrent->id;
				$aResults[$i]['interests'][] = $oNext->toArray($bAdmin);

				$z++;
				$oCurrent = $oNext;
			}

			$aResults[$i]['time'] = $totTime;
			$aResults[$i]['distance'] = $totDistance;
		}

		$i = 0;
		$best = -1;
		$bestID = -1;
		foreach ($aResults as $i => $res) {
			if ($best == -1 || $best > $res['time']) {
				$best = $res['time'];
				$bestID = $i;
			}
		}

		return ['best' => &$aResults[$bestID], 'results' => $aResults[$bestID]];
	}
}
