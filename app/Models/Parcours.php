<?php

namespace App\Models;

use App\Utils\ModelUtil;

class Parcours extends ModelUtil
{
	protected $table = 'parcours';
	protected $userStr = 'le parcours';
	protected static $sChildTable = 'interests';

	public $timestamps = true;

	protected $fillable = [
	  'id',
	  'cities_id',
	  'title',
	  'time',
	  'audio',
	  'description',
	  'audio_script',
	  'force_lang',
	  'state',
	  'color',
	  'created_at',
	  'updated_at'
	];

	protected $casts = [
	    'id' => 'string',
	    'title' => 'json',
	    'time' => 'json',
	    'audio' => 'json',
	    'description' => 'json',
	    'audio_script' => 'json'
	];

	protected $primaryKey = 'id';

	protected $aTranslateVars = [
	  'title', 
	  'time', 
	  'audio', 
	  'description',
	  'audio_script'
	];


	public static function getInstance() {
		return new Parcours;
	}

	public function save(Array $options=[]) {
		$bPrevState = (bool) @$this->original['state'];
		$bSuccess = Parent::save($options);
		
		$bCurState = $this->attributes['state'];

		if (!$bSuccess || !$bPrevState || $bPrevState == $bCurState) {
			return $bSuccess;
		}

		return $bSuccess;
	}

	public function delete() {
		//Interests::getByParcours();
	}

	public function loadParents() {
		if (empty($this->attributes['cities_id'])) {
			$this->attributes['city'] = null;
			return;
		}

		$city = Cities::Where('id', $this->attributes['cities_id'])->get(['id', 'title'])->first();
		if (!empty($city)) {
			$city->setLang($this->getLang());
		}

		$this->attributes['city'] = $city;
	}

	public static function search($query, $columns, $order = false) {
		$oModel = new Parcours;
		$query = preg_replace("/('{1})/", ("''"), $query);

		if (!empty($columns)) {
			foreach ($columns as &$col) {
				$col = 'parcours.'.$col;
			}
		}
		else{
			$columns = 'parcours.*';
		}

		$list = Parcours::Select($columns)
			->leftJoin('cities', 'parcours.cities_id', '=', 'cities.id')
			->whereRaw(
				"CONCAT(parcours.title->>'fr', parcours.title->'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				"CONCAT(parcours.audio_script->>'fr', parcours.audio_script->'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				"CONCAT(cities.title->>'fr', cities.title->'en') ILIKE '%{$query}%'"
			)
			->orWhereRaw(
				"CONCAT(parcours.description->>'fr', parcours.description->'en') ILIKE '%{$query}%'"
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
			$list->orderBy('parcours.title->fr', 'ASC');
		}
		elseif ($orderCol === 'score') {
			$list->orderByRaw("
				(
					(CONCAT(parcours.title->>'fr', parcours.title->>'en') 
					ILIKE 
					'%".$query."%')::int * 20
				) +
				(
					(CONCAT(parcours.audio_script->>'fr', parcours.audio_script->>'en') 
					ILIKE 
					'%".$query."%')::int * 15
				) +
				(
					(CONCAT(cities.title->>'fr', cities.title->>'en') 
					ILIKE 
					'%".$query."%')::int * 5
				) +
				(
					(CONCAT(parcours.description->>'fr', parcours.description->>'en') 
					ILIKE 
					'%".$query."%')::int *
				 3)

				".$orderWay."
			");
		}
		else {
			if (!in_array($orderCol, $oModel->aTranslateVars)) {
				$list->orderBy('parcours.'.$orderCol, $orderWay);
			}
			else{
				$list->orderBy('parcours.'.$orderCol.'->fr', $orderWay);
			}
		}

		return $list->get();
	}
}