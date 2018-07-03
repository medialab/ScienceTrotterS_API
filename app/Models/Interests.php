<?php
namespace App\Models;

use App\Utils\ModelUtil;
use App\Utils\MapApiUtil;

class Interests extends ModelUtil
{
	protected $table = 'interests';
	protected $userStr = 'le point d\'intérêt';

	public $timestamps = true;

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
		'distances',
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
		'distances' => 'json',
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
	
	protected $aOptionalFields = ['parcours_id', 'distances'];

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
		}

		return $this->attributes['parcours'];
	}

	public function loadParents() {
		$this->loadCity();
		$this->loadParcours();
	}

	public function save(Array $options=[]) {
		$prevGeo = empty($this->original['geoloc']) ? false : $this->original['geoloc'];
		$b = Parent::save($options);

		$attrs = &$this->attributes;

		if (!$b || empty($attrs['parcours_id']) || $attrs['geoloc'] === $prevGeo) {
			return $b;
		}

		$oParc = $this->loadParcours();
		$aInterests = $oParc->getInterests();

		$mapApi = new MapApiUtil();
		$aDistances = [];
		foreach ($aInterests as $oInt) {
			var_dump("## Handle ".$oInt->id);
			if ($oInt->id === $attrs['id']) {
				var_dump("## Is Same Than ".$this->id);
				continue;
			}
			elseif(empty($oInt->geoloc)) {
				var_dump("## No Geoloc Spécified");
				continue;
			}

			$aDist = $mapApi->getDistance($this, $oInt);
			if (!$aDist) {
				continue;
			}

			$aDist = ['time' => $aDist->duration, 'distance' => $aDist->distance];

			if (!is_array($oInt->attributes['distances'])) {
				$oInt->attributes['distances'] = [];
			}

			$aDistances[$oInt->id] = $aDist;

			$oInt->attributes['distances'][$this->attributes['id']] = $aDist;
			$oInt->save();
			var_dump("Updating: ".$oInt->title->fr);
		}

		$attrs['distances'] = $aDistances;
		return Parent::save($options);
	}

	public function delete() {
		if (empty($this->attributes['parcours_id'])) {
			return Parent::delete();
		}

		$id = $this->attributes['id'];
		$oParc = $this->loadParcours();

		$b = Parent::delete();
		if (!$b || !$oParc) {
			return $b;
		}

		$aInterests = $oParc->getInterests();
		foreach ($aInterests as $oInt) {
			if (is_array($oInt->distances)) {
				unset($oInt->distances[$id]);
				$oInt->save();
			}
		}

		return $b;
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
