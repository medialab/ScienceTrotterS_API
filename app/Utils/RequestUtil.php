<?php

namespace App\Utils;

use Illuminate\Http\Request;

class RequestUtil extends Request
{
	public static $sLang = null;
	public static $dSkip = null;
	public static $dLimit = null;
	public static $aOrder = null;

	public function __construct () {
		parent::__construct();
		$this->__init();
	}

	public function __init () {
		// ==> Set lang.
		if ($this->getParam('lang')) {
		   static::$sLang = strtolower($this->input('lang'));
		}

		// ==> Set limit.
		$limit = (int)$this->input('limit');
		if (!$limit) {
			$limit = 5000;
		}
		static::$dLimit = $limit;
		
		// ==> Set skip.
		$skip = (int)$this->input('skip');
		if (!$skip) {
			$skip = 0;
		}
		static::$dSkip = $skip;

		static::$aOrder = $this->getOrder();
	}

	public function getParam($sKey) {
		return $this->input($sKey);
	}

	public static function getParams() {
		return [
			'lang' => static::$sLang,
			'limit' => static::$dLimit,
			'skip' => static::$dSkip
		];
	}

	public function getLang () {
		return static::$sLang;
	}

	public function getlimit () {
		return static::$dLimit;
	}

	public function getSkip () {
		return static::$dSkip;
	}

	public function getOrder () {
		$aOrder = $this->input('order');
		if (empty($aOrder) || !is_array($aOrder) || empty($aOrder)) {
			return false;
		}

		return $aOrder;
	}

	public function getGeoloc() {
		$geoloc = $this->input('geoloc');
		if (!empty($geoloc)) {
			$aGeo = explode(';', $geoloc);
			if (count($aGeo) !== 2 || (float) $aGeo[0] == 0 || (float) $aGeo[1] == 0) {
				return false;
			}
		}
		else{
			$aGeo = false;
		}

		return $aGeo;
	}
}