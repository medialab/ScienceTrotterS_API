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
		   static::$sLang = strtolower($this->getParam('lang'));
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

		// ==> Set order.
		$aOrder = $this->getParam('order');

		if (!$aOrder) {
			$aOrder = false;
		}
		static::$aOrder = $aOrder;
	}

	public function getParam($sKey) {
		return empty($_GET[$sKey]) ? null : $_GET[$sKey];
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
		return static::$aOrder;
	}
}