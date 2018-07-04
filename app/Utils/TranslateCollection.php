<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * 
 */
class TranslateCollection extends Collection
{
	private $sCurLang = false;

	public function setLang($sLang = false) {
		$this->sCurLang = $sLang;

		foreach ($this->items as &$oModel) {
			$oModel->setLang($this->sCurLang);
		}

		return $this;
	}

	public function loadParents() {
		foreach ($this->items as &$oModel) {
			$oModel->loadParents();
		}
	}

	public function toArray($bAdmin = false) {
		$aResult = [];

		foreach ($this->items as &$oModel) {
			$aResult[] = $oModel->toArray($bAdmin);
		}

		return $aResult;
	}

	public function get($key, $default = NULL) {
		Parent::get($key, $default);
		$this->setLang($this->sCurLang);
	}

	public function delete() {
		$b = true;
		foreach ($this->items as &$oModel) {
			$b = $b && $oModel->delete();
		}

		return $b;
	}
}