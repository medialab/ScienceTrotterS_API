<?php
namespace App\Models;

use App\Utils\ModelUtil;

class Interests extends ModelUtil
{
	protected $table = 'interests';
	protected $userStr = 'le point d\'intérêt';
	protected $distances = false;

	public $timestamps = true;


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

	protected $aUploads = ['header_image', 'audio', 'gallery_image'];
	
	protected $aOptionalFields = ['parcours_id'];

	public function __get($sVar) {
		switch ($sVar) {
			case 'distances':
				return $this->getDistances();
				break;
		}

		return Parent::__get($sVar);
	}

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

	public static function getInstance() {
		return new Interests;
	}

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

	public function loadParents() {
		$this->loadCity();
		$this->loadParcours();
	}

	public function save(Array $options=[]) {
		$prevGeo = empty($this->original['geoloc']) ? false : $this->original['geoloc'];
		$prevParc = empty($this->original['parcours_id']) ? false : $this->original['parcours_id'];


		$attrs = &$this->attributes;

		$bGeoSame = $attrs['geoloc'] === $prevGeo;
		$bParcoursUpdated = $prevParc === @$attrs['parcours_id'];		
		
		if ($bGeoSame && !$bParcoursUpdated) {
			return Parent::save($options);
		}
		else{
			InterestWay::deleteByInterest($this);
		}

		if (empty($this->geoloc) || empty($attrs['parcours_id'])) {
			$this->distances = [];
			return Parent::save($options);
		}

		//$this->getDistances();
		
		/*
			$oParc = $this->loadParcours();

			if (is_null($oParc)) {
				return $b;
			}

			$aDistances = [];
			$mapApi = new MapApiUtil();
			$aInterests = $oParc->getInterests();

			foreach ($aInterests as $oInt) {
				if ($oInt->id === $attrs['id']) {
					continue;
				}
				elseif(empty($oInt->geoloc)) {
					continue;
				}

				$aDist = $mapApi->getDistance($this, $oInt);
				if (!$aDist) {
					continue;
				}

				$aDist = [
					'time' => $aDist->duration, 
					'distance' => $aDist->distance
				];

				$oWay = InterestWay::byInterests($this, $oInt);
				if (is_null($oWay)) {
					$oWay = new InterestWay;
					$oWay->int1 = $this->id;
					$oWay->int2 = $intID;
				}

				if ($oWay->time !== $aDist['time'] || $oWay->distance !== $aDist['distance']) {
					$oWay->time = $aDist['time'];
					$oWay->distance = $aDist['distance'];

					$oWay->save();
				}

				$aDistances[$oWay->id] = $oWay;
				$oInt->refresh();
			}

			$this->distances = $aDistances;
		*/
	
		//var_dump("UPDATING WAYS");
		$this->distances = InterestWay::updateByInterest($this);
		if ($this->distances === false) {
			return $b;
		}

		return Parent::save($options);
	}

	public function delete() {
		if (empty($this->attributes['parcours_id'])) {
			return Parent::delete();
		}

		if (!empty($this->attributes['parcours_id'])) {
			$b = InterestWay::deleteByInterest($this);
			if (!$b) {
				return false;
			}
		}

		$b = Parent::delete();
		return $b;
	}

	public function enable($b = true) {
		$aErrors = Parent::enable($b);
		if (!$b || !empty($aErrors)) {
			return $aErrors;
		}

		$oWayList = InterestWay::byInterest($this);
		$aErrors = [];

		foreach ($oWayList as &$oWay) {
			if ($oWay->time > 0) {
				continue;
			}

			$otherId = $oWay->int1 === $this->id ? $oWay->int1 : $oWay->int;
			$oInt = Interests::Where([['id', '=', $otherId], ['state', '=', true]])->get(['title']);
			if (!$oInt) {
				continue;
			}

			$oInt->setLang('fr');
			$aErrors[] = $oInt->title;
		}

		if (empty($aErrors)) {
			$this->errorMsg = null;
			return true;
		}

		$this->errorMsg = 'Impossible d\'activer '.$this->userStr.'. Il semblerait que le trajet est impossible avec: ';

		$this->errorMsg .= '<ul>';
		foreach ($aErrors as $name) {
			$this->errorMsg .= '<li>'.$name.'</li>';
		}
		$this->errorMsg .= '</ul>';
		return false;
	}

	public static function byParcours($id, $oRequest, $bAdmin = false) {
		$where = [['parcours_id', '=', $id]];
		if (!$bAdmin) {
		    $where[] = ['state', '=', 'true'];
		}

		$oModelList = Self::list($oRequest, $bAdmin);
		$oModelList->where($where);

		return $oModelList;
	}

	public static function search($query, $columns, $order = false) {
		$oModel = new Interests;
		$query = preg_replace("/('{1})/", ("''"), $query);

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

			->whereRaw(
				"CONCAT(interests.title->>'fr', interests.title->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				"CONCAT(interests.address->>'fr', interests.address->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				"CONCAT(interests.audio_script->>'fr', interests.audio_script->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				"CONCAT(parcours.title->>'fr', parcours.title->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				"CONCAT(interests.description->>'fr', interests.description->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				"CONCAT(parcours.description->>'fr', parcours.description->>'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
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

		if (!$order || ($orderCol !== "score" && !in_array($orderCol, $oModel->fillable))) {
			$list->orderBy('interests.title->fr', 'ASC');
		}
		elseif ($orderCol === 'score') {
			$list->orderByRaw("
				((
					CONCAT(interests.title->>'fr', interests.title->>'en') ILIKE '%".$query."%')::int * 25
				) +
				((
					CONCAT(interests.address->>'fr', interests.address->>'en') ILIKE '%".$query."%')::int * 20
				) +
				((
					CONCAT(interests.audio_script->>'fr', interests.audio_script->>'en') ILIKE '%".$query."%')::int * 15
				) +
				((
					CONCAT(interests.description->>'fr', interests.description->>'en') ILIKE '%".$query."%')::int * 13
				) +

				((
					CONCAT(parcours.title->>'fr', parcours.title->>'en') ILIKE '%".$query."%')::int * 10
				) +

				((
					CONCAT(parcours.description->>'fr', parcours.description->>'en') ILIKE '%".$query."%')::int * 3
				) +
				((
					CONCAT(cities.title->>'fr', cities.title->>'en') ILIKE '%".$query."%')::int * 5
				)

				".$orderWay."
			");
		}
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
}
