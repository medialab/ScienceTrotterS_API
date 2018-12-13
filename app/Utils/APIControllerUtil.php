<?php
namespace App\Utils;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Utils\RequestUtil as Request;
use App\Utils\CheckerUtil as Check;
use App\Models\Parcours;
use App\Models\ListenAudio;
use Illuminate\Database\QueryException AS Exception;

class APIControllerUtil extends BaseController
{
	protected $sModelClass;
	protected $bAdmin = false;

	// Récupération De la Classe Du Model
	public function getClass() {
		return 'App\Models\\'.$this->sModelClass;
	}

	public function byId(Request $oRequest, $sInterestId) {
		return $this->get($sInterestId, $oRequest);
	}

	public function latest(RequestUtil $oRequest) {
		$class = $this->getClass();
		$sLang = $oRequest->input('lang');
		$oModel = $class::Take(1)->where('state', true)->orderBy('updated_at', 'desc');

		return $oModel;
	}

	/**
	 * Écrit Dans les Logs du Web Server actif (Apache/ngix)
	 * @param  String $sMethod Methode Utilisée par la requête
	 */
	private function logSqlError(Exception $e) {
		$sDate = date('Y-m-d H:i:d');

		$sQuery = $e->getSql();
		$aBindings = var_export($e->getBindings(), true);
		$sMessage = $e->getMessage();

		$sMsg = "
			============== API: {$sDate} ==============
				Type: Fail To Execute A SQL Request From Api Controller: {$this->sModelClass}

				++++ Error Detail:
					-- SQL Message:
						{$sMessage}

					-- SQL Request:
						{$sQuery}

					-- Query Bindings:
						{$aBindings}
		";

		$sMsg = preg_replace("/\t{3}/", "", $sMsg);
		error_log($sMsg);
	}


	/**
	 * Retourne Une Liste de Models
	 * @param  Request $oRequest Requete
	 * @param  boolean $bAsList  Retourne Le QueryNuilder Au lieu du Résultat De Requete
	 * @return Collection || QueryBuilder            Liste des Model Ou Constructeur De Requete SQL
	 */
	public function list(Request $oRequest, $bAsList=false) {
		$class = $this->getClass();
		$oModelList = ($class)::list($oRequest, $this->bAdmin);
		if ($bAsList) {
			return $oModelList;
		}

		$sLang = $oRequest->input('lang');
		$columns = $oRequest->input('columns');
		$parents = $oRequest->input('parents');

		try {

			$oModelList = $oModelList->get($columns);

			// Définition De La Langue
			if ($sLang) {
				$oModelList->setLang($sLang);
			}

			// Chargement Des Parents
			if ($parents) {
				$oModelList->loadParents();
			}

			return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
		} catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To List: '.$this->sModelClass, [], 500);
		}
	}

	/**
	 * Reponse Success En Json
	 * @param  Mixed $result  Resultat
	 * @param  String $message Message
	 * @return String          Json
	 */
	public function sendResponse($result, $message = null)
	{
		if (!empty($_POST['callback'])) {
			$cl = $_POST['callback'];
		}
		elseif (!empty($_GET['callback'])) {
			$cl = $_GET['callback'];
		}
		else{
			$cl = false;
		}

		$aResponse = [
			'success' => true,
			'data'    => $result,
			'message' => $message,
		];

		if ($this->bAdmin) {
			$aResponse['token'] = _USER_TOKEN_;
		}

		if (!empty($message)) {
			$this->logError($aResponse, true);
		}

		if ($cl) {
			echo $cl.'('.json_encode($aResponse).')';
			exit;   
		}


		header('Content-Type: application/json');
		return response()->json($aResponse, 200);
	}
	


	/**
	 * Écrit Dans les Logs du Web Server actif (Apache/ngix)
	 */
	private function logError($aResponse, $bWarning=false) {
		$sDate = date('Y-m-d H:i:d');

		$sType = !$bWarning ? 'Fail To Execute A Request From Admin' : 'Request From Admin Executed With Warnings';
		$oReq = new RequestUtil();
		$sResponse = var_export($aResponse['data'], true);
		$sMessage = str_replace(['<ul>', '</ul>', '<li>', '</li>'], [' ', '', '', ', '], var_export($aResponse['message'], true));

		$sMsg = "
			============== API: {$sDate} ==============
				Type: {$sType}

				++++ Api Error:
					-- method: {$_SERVER['REQUEST_METHOD']}
					-- Url: {$_SERVER['REQUEST_URI']}
					-- Params: {$oReq}
					-- Messages: {$sMessage}
					-- Response: {$sResponse}
		";

		$sMsg = preg_replace("/\t{3}/", "", $sMsg);
		error_log($sMsg);
	}

	/**
	 * Reponse Error En Json
	 * @param  String $error  Msg Erreur
	 * @param  Array $errorMessages Array De Messages
	 * @param  Int $dCode Code Erreur HTTP
	 * @return String          Json
	 */
	public function sendError($error, $errorMessages = [], $dCode = 400) {
		if (!empty($_POST['callback'])) {
			$cl = $_POST['callback'];
		}
		elseif (!empty($_GET['callback'])) {
			$cl = $_GET['callback'];
		}
		else{
			$cl = false;
		}

		$aResponse = [
			'success' => false,
			'message' => $error,
		];

		$aResponse['data'] = $errorMessages;

		$this->logError($aResponse);

		if ($this->bAdmin) {
			$aResponse['token'] = _USER_TOKEN_;
		}

		if ($cl) {
			echo $cl.'('.json_encode($aResponse).')';
			exit;   
		}

		return response()->json($aResponse, $dCode);
	}

	/**
	 * Mise à Jour D'un Modele
	 * @param  Request $oRequest Requete
	 * @return Json            Response
	 */
	public function update(Request $oRequest) {
		$class = $this->getClass();

		// Récupération Du Model
		$id = $oRequest->input('id');        
		$oModel = call_user_func($class.'::where', 'id', $id)->first();
		
		
		// Aucun Model Trouvé => 404
		if (!$oModel) {
			return $this->sendError('Not Found', ['Can\'t found '.$class.' With ID:'.$id], 404);
		}

		// Définition de la langue
		$sLang = $aData = $oRequest->input('lang');
		$oModel->setLang($sLang);

		$aErrors = [];
		$aUpdates = [];
		// Récupération des Données
		$aData = $oRequest->input('data');

		if (isset($aData['state']) && !is_bool($aData['state'])) {
			$aData['state'] = $aData['state'] === 'true';
		}

		// Récupération Du Status Actuel
		$bCurState = $oModel->state;

		try {
			$oModel->updateData($aData);
			// Mise à Jour

			$msg = null;
			// Si Le Status Demandé n'est Pas Possible
			if ($aData['state'] !== $oModel->state) {
				// Récupération Du Message D'erreur
				$msg = $oModel->getError();
			}

			// Si Le Model était In-Actif Ou Aucune Erreur Détéctée
			if (!$bCurState || empty($msg)) {
				// On Save
				if ($oModel->save()) {
					$oModel->setLang($sLang);

					// Récupéartion  Du Msg Warning
					$msg2 = $oModel->getError();

					if (!empty($msg2)) {
						$msg = $msg2;
					}

					return $this->sendResponse($oModel->toArray($this->bAdmin), $msg);
				}
				else{
					// Récupéartion  Du Msg Erreur
					$msg = $oModel->getError();
				}
			}

				// Si Le Message Est Vide
			if (empty($msg)) {
				$msg = 'Fail To Query Update';
			}

			return $this->sendError($msg, null, 200);
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			$oModel->defineLang($sLang);
			return $this->sendError('Fail To Update: '.$this->sModelClass.': "'.$oModel->title.'"', [], 500);
		}
	}

	/**
	 * Insertion D'un Model
	 * @param  Request $oRequest Requete
	 * @return Json            Resultat
	 */
	public function insert(Request $oRequest) {
		$class = $this->getClass();
		$aData = $oRequest->input('data');

		// Le Titre Est Obligatoire
		if (empty($aData['title'])) {
			return $this->sendError('Error: Missing '.$class.' title', ['Error: Missing '.$class.' Title'], 400);
		}

		// Selection De la Langue
		$sLang = $oRequest->input('lang');
		if (!$sLang) {
			return $this->sendError('Fail To Query Insert', ['Lang Param is Requested'], 400);
		}

		$oModel = new $class;
		$oModel->setLang($sLang);
		
		if (isset($aData['state']) && !is_bool($aData['state'])) {
			$aData['state'] = $aData['state'] === 'true';
		}


		try {
			// Inertion Des Données
			$oModel->updateData($aData);
			
			$msg = null;
			// Si Le Status Demandé n'est Pas Possible
			if ($aData['state'] !== $oModel->state) {
				// Rcupération Du Msg D'erreur
				$msg = $oModel->getError();
			}

			// Sauvegarde Du Model
			if ($oModel->save()) {
				// Récupéartion  Du Msg Warning
			   $msg2 = $oModel->getError();
			   //var_dump("Msg: ".$msg, "Msg2: ".$msg2);
			   if (!empty($msg2)) {
				   $msg = $msg2;
			   }
			   //var_dump("final Msg: ".$msg);
				return $this->sendResponse($oModel->toArray($this->bAdmin), $msg);
			}

			return $this->sendError('Fail To Query Insert', null, 400);
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			$oModel->defineLang($sLang);
			return $this->sendError('Fail To Insert: '.$this->sModelClass.': "'.$oModel->title.'"', [], 500);
		}
	}

	/**
	 * Suppression d'un Model
	 * @param  Request $oRequest Requete
	 * @return Json            Résultat
	 */
	public function delete(Request $oRequest) {
		$this->validate($oRequest, [
			'id' => 'required',
		]);

		// Récupération Du Model
		$id = $oRequest->input('id');
		if (!Check::is_uuid_v4($id)) {
			return $this->sendError('Bad ID', ['id param must be string in uuid_v4 format'], 400);
		}

		$class = $this->getClass();
		
		try {
			$oModel = call_user_func($class.'::where', 'id', $id)->first();

			// Model Trouvé
			if ($oModel) {
				// Suppression
				if ($oModel->delete()) {
					return $this->sendResponse(true, null);
				}

				return $this->sendError('Fail To Query Delete', ['Fail To Query Delete'], 400);
			}
			// Model Introuvable  => 404
			else{
				return $this->sendError('Not Found', ['Fail To Find '.$class.' with ID: '.$id], 404);
			}
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			$sMsg = 'Fail To Delete: '.$this->sModelClass;
			if ($oModel) {
				$sMsg .= ': "'.$oModel->title.'"';
			}

			return $this->sendError($sMsg, [], 500);
		}
	}

	/**
	 * Récupération Par ID
	 * @param  String  $id       ID Model
	 * @param  Request $oRequest Requete
	 * @return Json            Modelz
	 */
	public function get($id, Request $oRequest) {
		if (!preg_match('/[a-z0-9]{8}-([a-z0-9]{4}-){3}[a-z0-9]{12}/', $id)) {
			return $this->sendError('Bad  ID', ['L\'identifiant est invalide'], 400);
		}

		$columns = $oRequest->input('columns');
		$sLang = $oRequest->input('lang');
		$class = $this->getClass();

		try {
			// Récupération Du Model
			if (!$this->bAdmin) {
				// Limitation Du Context Public
				$oModelList = ($class)::Where([['state', '=', true], ['id', '=', $id]]);

				if ($sLang) {
					$oModelList->where(function($query) use ($sLang){
						$query->where('force_lang', $sLang)
							  ->orWhere('force_lang', '')
							  ->orWhereNull('force_lang')
						;
					});
				}

				$oModel = $oModelList->get($columns)->first();
			}
			else{
				$oModelList = $class::Where('id', $id);

				// Selection De La Langue
				$oModel = $oModelList->get($columns)->first();
				if ($sLang) {
					$oModel->setLang($sLang);
				}
			}

			// Model Introuvable => 404
			if (is_null($oModel)) {
				return $this->sendError('Not Found', ['Model introuvable'], 404);
			}

			return $this->sendResponse($oModel->toArray($this->bAdmin), null)->content();
		} 
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To Delete: '.$this->sModelClass.': '.$id, [], 500);
		}
	}

	/**
	 * Récupération Model Par Ville
	 * @param  String  $id       ID Ville
	 * @param  Request $oRequest Requete
	 * @return Json            Liste Models
	 */
	public function getByCity($id, Request $oRequest) {
		$class = $this->getClass();
		$skip = $oRequest->getSkip();
		$limit = $oRequest->getLimit();
		$sLang = $oRequest->input('lang');
		$columns = $oRequest->input('columns');


		try {
			// Récupération Des Modèles
			if (!$this->bAdmin) {
				// Limitation Context Public
				$oModelList = ($class)::Where(['state', true]);
				$oModelList->take($limit);
			}
			else{
				$oModelList = call_user_func($class.'::take', $limit);
			}
			
			$oModelList = $oModelList->skip($skip)->get($columns);

			// Selection De la Langue
			if ($sLang) {
				foreach ($oModelList as $key => &$oModel) {
					$oModel->setLang($sLang);
				}
			}
			

			return $this->sendResponse($oModel->toArray($this->bAdmin), null)->content();
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To Get '.$this->sModelClass.' By City: '.$id, [], 500);
		}
	}

	/**
	 * Récupération Model Par Parcours
	 * @param  String  $id       ID Parcours
	 * @param  Request $oRequest Requete
	 * @return Json            Liste Models
	 */
	public function byParcoursId(Request $oRequest=NULL, $id) {
		if (is_null($oRequest)) {
			$oRequest = new Request();
		}

		$sLang = $oRequest->input('lang');
		$columns = $oRequest->input('columns');

		$class = $this->getClass();

		try {
			$oModelList = ($class)::byParcours($id, $oRequest, $this->bAdmin);
			$oModelList = $oModelList->get($columns);

			// Selection De la Langue
			if ($sLang) {
				$oModelList->setLang($sLang);
			}

			return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To Get '.$this->sModelClass.' By Parcours: '.$id, [], 500);
		}
	}

	/**
	 * Equivalant ByCity
	 */
	public function byCityId(Request $oRequest=NULL, $id) {
		if (is_null($oRequest)) {
			$oRequest = new Request();
		}

		$skip = $oRequest->getSkip();
		$limit = $oRequest->getLimit();
		$aGeo = $oRequest->getGeoloc();
		$sLang = $oRequest->input('lang');
		$aOrder = $oRequest->input('order');
		$columns = $oRequest->input('columns');

		$class = $this->getClass();
		$oModel = new $class;

		$where = [[$oModel->table.'.cities_id', '=', $id]];

		if (!$this->bAdmin) {
			$where[] = [$oModel->table.'.state', '=', 'true'];
		}

		$class = $this->getClass();

		try {
			if (!$aGeo) {
				$oModelList = ($class)::list($oRequest, $this->bAdmin, $where);
				//var_dump($oModelList->toSql());
				$oModelList = $oModelList
					->where($where)
					->take($limit)
					->skip($skip)
					->get($columns)
				;

				if ($sLang) {
					$oModelList->setLang($sLang);
				}

				return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
			}
			else {
				$oResult = $class::closest($aGeo, false, $id, $sLang, $columns, $aOrder);
				if ($oResult instanceOf ModelUtil) {
					if ($sLang) {
						$oResult->setLang($sLang);
					}

					$oResult = $oResult->toArray($this->bAdmin);
				}
				/*elseif ($oResult && $sLang){
					foreach ($oResult as &$oRes) {
						var_dump($oRes);
						$oRes->setLang($sLang);
					}
				}*/
				
				return $this->sendResponse($oResult, null)->content();
			}
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To Get '.$this->sModelClass.' By City: '.$id, [], 500);
		}
	}

	/**
	 * Récupération Point Hors PArcours
	 * @param  Request|null $oRequest Requete
	 * @param  String       $id       ID => 'no-city'
	 * @return Json                 Liste DesPoints
	 */
	public function byNoParcours(Request $oRequest=NULL, $id) {
		if (is_null($oRequest)) {
			$oRequest = new Request();
		}

		$skip = $oRequest->getSkip();
		$limit = $oRequest->getLimit();
		$sLang = $oRequest->input('lang');
		$columns = $oRequest->input('columns');


		// Points Sans Ville
		$bNoCity = $id === 'no-city';
		if (!$bNoCity) {
			$where = [['cities_id', '=', $id]];
		}
		else{
			$where = [];
		}

		// Limitation Context Public
		if (!$this->bAdmin) {
			$where[] = ['state', '=', 'true'];
		}

		try {
			// Récupération De la Liste
			$oModelList = $this->list($oRequest, true);

			// Limitation De la Liste
			$oModelList = $oModelList
				->where($where)
				->whereNull('parcours_id')
			;

			// Sans Ville
			if ($bNoCity) {
				$oModelList->whereNull('cities_id');
			}

			if ($sLang) {
				$oModelList->where(function($query) use ($sLang) {
					$query->Where('interests.force_lang', '')
						->orWhereNull('interests.force_lang')
						->orWhere('interests.force_lang', $sLang);
				});
				
				$oModelList->leftJoin('cities', 'interests.cities_id', '=', 'cities.id');
				$oModelList->where(function($query) use ($sLang) {
					$query->Where('cities.force_lang', '')
						->orWhereNull('cities.force_lang')
						->orWhere('cities.force_lang', $sLang);
				});
			}

			// Récupération
			$oModelList = $oModelList->take($limit)
				->skip($skip)
				->get($columns)
			;

			// Selection DDe la Langue
			if ($sLang) {
				$oModelList->setLang($sLang);
			}

			return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To Get '.$this->sModelClass.' By No Parcours In City: '.$id, [], 500);
		}
	}

	/**
	 * Recupération Model Sans Ville
	 * @param  Request|null $oRequest Request
	 * @return Json                 Liste Models
	 */
	public function byNoCity(Request $oRequest=NULL) {
		if (is_null($oRequest)) {
			$oRequest = new Request();
		}

		$class = $this->getClass();
		$oModel = new $class;

		$skip = $oRequest->getSkip();
		$limit = $oRequest->getLimit();
		$sLang = $oRequest->input('lang');
		$columns = $oRequest->input('columns');
		$table = $oModel->table;

		// Ré-écriture Colones
		foreach ($columns as &$sCol) {
			$sCol = $table.'.'.$sCol;
		}


		try {
			// Récupération de la liste
			$oModelList = $this->list($oRequest, true)
				->leftJoin('cities', $table.'.cities_id', '=', 'cities.id')
				
				->where(function($query) {
				$query->whereNull('cities_id')
					  ->orWhereNull('cities.id')
				;
			});

			// Limitation Context Public
			if (!$this->bAdmin) {
				$oModelList->where(['state', '=', 'true']);
			}

			// Récupération
			$oModelList = $oModelList
				->take($limit)
				->skip($skip)
				->get($columns)
			;

			/*var_dump($oModelList->toSql());
			$oModelList = $oModelList->get($columns);
			var_dump($oModelList);*/


			// Séléction de la langue
			if ($sLang) {
				$oModelList->setLang($sLang);
			}
			
			return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To Get '.$this->sModelClass.' By No City', [], 500);
		}
	}

	/**
	 * Recheche D'un Model
	 * @param  Request $oRequest Request
	 * @return Json            Liste Models
	 */
	public function search(Request $oRequest) {
		$class = $this->getClass();
		$sLang = $oRequest->input('lang');
		$order = $oRequest->input('order');
		$search = $oRequest->input('search');
		$parents = $oRequest->input('parents');
		$columns = $oRequest->input('columns');
		
		try {
			// Appel De la Rcherche
			$oModelList = ($class)::search($search, $columns, $order);

			// Application De la Langue
			if ($sLang) {
				$oModelList->setLang($sLang);
			}


			// Récupératation Des Parents
			if ($parents) {
				$oModelList->loadParents();
			}

			return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To Search '.$this->sModelClass, [], 500);
		}
	}

	public function listen(Request $oRequest=NULL, $id) {
		if (is_null($oRequest)) {
			$oRequest = new Request();
		}

		$sLang = $oRequest->input('lang');
		$phone_id = $oRequest->input('phone_id');
		
		if (!$sLang) {
			return $this->sendError('lang param must be specified.', [], 400);
		}

		if (!$phone_id) {
			return $this->sendError('phone_id param must be specified.', [], 400);
		}

		$class = $this->getClass();
		
		try {
			$oModelList = $class::Where([['id', '=', $id], ['state', '=', true]]);
			$oModel = $oModelList->get()->first();
			if (is_null($oModel)) {
				return $this->sendError('Not Found', ['Fail To Find '.$class.' with id: '.$id], 404);
			}

			$oModel->setLang($sLang);
			$file = $oModel->audio;

			if (is_null($file)) {
				return $this->sendResponse(true);
			}

			$b = ListenAudio::listen($sLang, $phone_id, $id, $class, $file);
			if ($b) {
				return $this->sendResponse(true);
			}

			return $this->sendError('Fail To Save', [], 400);
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail To Update Listen For '.$this->sModelClass.': '.$id, [], 500);
		}
	}

	public function listenCount(Request $oRequest=NULL, $id) {
		$sLang = $oRequest->input('lang');

		if (!$sLang) {
			return $this->sendError('Lang param is required.', [], 400);
		}

		$class = $this->getClass();

		try {
			$oModelList = $class::Where([['id', '=', $id]]);
			$oModel = $oModelList->get()->first();
			if (is_null($oModel)) {
				return $this->sendError('Not Found', ['Fail To Find '.$class.' with id: '.$id], 404);
			}

			$oModel->setLang($sLang);

			$oModelList = ListenAudio::Where([
				['lang', '=', $sLang],
				['cont_id', '=', $id],
				['cont_type', '=', $class],
				['file', '=', $oModel->audio]
			]);

			$dRes = count($oModelList->get());
			
			return $this->sendResponse($dRes);
		}
		catch (Exception $e) {
			$this->logSqlError($e);
			return $this->sendError('Fail Count Listen For  '.$this->sModelClass.': '.$id, [], 500);
		}
	}
}
