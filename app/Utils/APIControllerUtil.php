<?php

namespace App\Utils;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Utils\RequestUtil as Request;
use App\Models\Parcours;
use App\Models\ListenAudio;

class APIControllerUtil extends BaseController
{
    protected $sModelClass;
    protected $bAdmin = false;

    // Récupération De la Classe Du Model
    public function getClass() {
        return 'App\Models\\'.$this->sModelClass;
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
        if ($cl) {
            echo $cl.'('.json_encode($aResponse).')';
            exit;   
        }


        header('Content-Type: application/json');
        return response()->json($aResponse, 200);
    }
    
    /**
     * Reponse Error En Json
     * @param  String $error  Msg Erreur
     * @param  Array $errorMessages Array De Messages
     * @param  Int $dCode Code Erreur HTTP
     * @return String          Json
     */
    public function sendError($error, $errorMessages = [], $dCode = 400)
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
            'success' => false,
            'message' => $error,
        ];

        if (! empty($errorMessages)) {
            $aResponse['data'] = $errorMessages;
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

        // Mise à Jour
        $oModel->updateData($aData);

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
        $class = $this->getClass();
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
            return $this->sendError('Fail To Query Delete', ['Fail To Find '.$class.' with ID: '.$id], 400);
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

        // Récupération Du Model
        if (!$this->bAdmin) {
            // Limitation Du Context Public
            $oModelList = 
                ($class)::Where([['state', '=', true], ['id', '=', $id]])->where(function($query) use ($sLang){
                    $query->where('force_lang', $sLang)
                          ->orWhere('force_lang', '')
                          ->orWhereNull('force_lang')
                    ;
                })
            ;

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
        $oModelList = ($class)::byParcours($id, $oRequest, $this->bAdmin);
        $oModelList = $oModelList->get($columns);

        // Selection De la Langue
        if ($sLang) {
            $oModelList->setLang($sLang);
        }

        return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
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
        
        //var_dump($columns);

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

    public function listen(Request $oRequest=NULL, $id) {
        if (is_null($oRequest)) {
            $oRequest = new Request();
        }

        $sLang = $oRequest->input('lang');
        $phone_id = $oRequest->input('phone_id');
        
        if (!$sLang) {
            return $this->sendError('lang param must be specified.' [], 400);
        }

        if (!$phone_id) {
            return $this->sendError('phone_id param must be specified.' [], 400);
        }

        $class = $this->getClass();

        $b = ListenAudio::listen($sLang, $phone_id, $id, $class);
        if ($b) {
            return $this->sendResponse(true);
        }

        return $this->sendError('Fail To Save', [], 400);
    }

    public function listenCount(Request $oRequest=NULL, $id) {
        $sLang = $oRequest->input('lang');

        if (!$sLang) {
            return $this->sendError('Lang param is required.', [], 400);
        }

        $class = $this->getClass();
        $oModelList = ListenAudio::Where([
            ['lang', '=', $sLang],
            ['cont_id', '=', $id],
            ['cont_type', '=', $class]
        ]);

        $dRes = count($oModelList->get());
        
        return $this->sendResponse($dRes);
    }
}
