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
		$limit = (int)$this->getParam('limit');
		if (!$limit) {
			$limit = 5000;
		}
		static::$dLimit = $limit;
		
		// ==> Set skip.
		$skip = (int)$this->getParam('skip');
		if (!$skip) {
			$skip = 0;
		}
		static::$dSkip = $skip;

		static::$aOrder = $this->getOrder();
	}

	public function getParam($sKey) {
		$res = $this->input($sKey);
		if (empty($res) && !empty($_GET[$sKey])) {
			$res = $_GET[$sKey];
		}
		elseif (empty($res) && !empty($_POST[$sKey])) {
			$res = $_POST[$sKey];
		}

		return $res;
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
		$aOrder = $this->getParam('order');
		if (empty($aOrder) || !is_array($aOrder) || empty($aOrder)) {
			return false;
		}

		return $aOrder;
	}

	public function getGeoloc() {
		$geoloc = $this->getParam('geoloc');
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

	public function __toString() {
		$aData = [];


		if (!empty($this->getParam('lang'))) {
			$aData['lang'] = $this->getParam('lang');
		}

		if (!empty($this->getParam('skip'))) {
			$aData['skpi'] = $this->getParam('skip');
		}

		if (!empty($this->getParam('limit'))) {
			$aData['limit'] = $this->getParam('limit');
		}

		$aOrder = $this->getOrder();
		if (!empty($aOrder)) {
			$aData['order'] = var_export($aOrder, true);
		}

		$aGeo = $this->getGeoloc();
		if (!empty($aGeo)) {
			$aData['geoloc'] = var_export($aGeo, true);
		}

		return var_export($aData, true);
	}
}