<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Ajout de la Fonction Tradution à L'objet Collection
 */
class TranslateCollection extends Collection
{
	/**
	 * Langue Actuelle
	 */
	private $sCurLang = false;

	/**
	 * Séléction de la Langue
	 * @param String $sLang La Langue à Séléctionné
	 */
	public function setLang($sLang = false) {
		$this->sCurLang = $sLang;

		foreach ($this->items as &$oModel) {
			$oModel->setLang($this->sCurLang);
		}

		return $this;
	}

	/**
	 * Séléction de la Langue
	 * @param String $sLang La Langue à Séléctionné
	 */
	public function defineLang($sLang = false) {
		$this->sCurLang = $sLang;

		foreach ($this->items as &$oModel) {
			$oModel->defineLang($this->sCurLang);
		}

		return $this;
	}

	/**
	 * Charge les Parents
	 */
	public function loadParents() {
		foreach ($this->items as &$oModel) {
			$oModel->loadParents();
		}
	}

	/**
	 * Tranformation Des Résulatats En Tableaux
	 * @param  boolean $bAdmin Context Est Privé
	 * @return Array          Le Tableau des Model
	 */
	public function toArray($bAdmin = false) {
		$aResult = [];

		foreach ($this->items as &$oModel) {
			$aResult[] = $oModel->toArray($bAdmin);
		}

		return $aResult;
	}

	/**
	 * Récupère un Model par ID
	 * @param  String $key     ID
	 * @param  Mixed $default Retour Si aucun Résultat
	 * @return Model          Le Résultat
	 */
	public function get($key=null, $default = NULL) {
		$res = Parent::get($key, $default);
		$this->setLang($this->sCurLang);

		return $res;
	}

	/**
	 * Supprime tout les Modèles Trouvés
	 * @return Bool Success
	 */
	public function delete() {
		$b = true;
		foreach ($this->items as &$oModel) {
			$b = $b && $oModel->delete();
		}

		return $b;
	}

	/**
	 * Sauvegarde Tout les Modèle Trouvés
	 * @param  Array|array $options Paramètre Lumen
	 * @return Bool               Success
	 */
	public function save(Array $options=[]) {
		$b = true;
		foreach ($this->items as &$oModel) {
			$b = $b && $oModel->save($options);
		}
		return $b;
	}
}