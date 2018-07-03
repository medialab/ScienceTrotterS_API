<?php

namespace App\Utils;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Utils\RequestUtil as Request;

class APIControllerUtil extends BaseController
{
    protected $sModelClass;
    protected $bAdmin = false;

    public function getClass() {
        return 'App\Models\\'.$this->sModelClass;
    }

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

        if ($sLang) {
            $oModelList->setLang($sLang);
        }

        if ($parents) {
            $oModelList->loadParents();
        }

        return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();

        /*
            if ($sLang) {
                $oModelList = ($class)::where(function($query) use ($sLang){
                    if ($sLang && !$this->bAdmin) {
                        $query->Where('force_lang', '')
                          ->orWhereNull('force_lang')
                          ->where('force_lang', $sLang);
                    }
                });
            }
            else{
                $oModelList = ($class)::whereNotNull('id');
            }

            if (!$this->bAdmin) {
                $oModelList->where('state', true);
            }

            if ($bAsList) {
                return $oModelList;
            }

            $oModelList->take($limit)->skip($skip);

            //var_dump($aOrder);
            if ($aOrder && count($aOrder) === 2) {
                //var_dump("ORDERING", $aOrder);
                $oModel = new $class;

                $aOrderCol = $aOrder[0];
                $sOrderWay = $aOrder[1];

                if (!is_array($aOrderCol)) {
                    $aOrderCol = [$aOrderCol];
                }

                foreach ($aOrderCol as $sOrderCol) {
                    if (in_array($sOrderCol, $oModel->aTranslateVars)) {
                        if ($oModel->force_lang) {
                            $sOrderCol .= '->'.$oModel->force_lang.'' ;
                        }
                        elseif ($sLang) {
                            $sOrderCol .= '->'.$sLang.'' ;
                        }
                        else{
                            $sOrderCol .= '->fr' ;
                        }
                    }

                    $oModelList->orderBy($sOrderCol, $sOrderWay);
                }
            }

            $oModelList = $oModelList->get($columns);

            if ($sLang) {
                $oModelList->setLang($sLang);
            }

            if ($parents) {
                $oModelList->loadParents();
            }

            return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
        */
    }

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

    public function update(Request $oRequest) {
        $class = $this->getClass();

        $id = $oRequest->input('id');        
        $oModel = call_user_func($class.'::where', 'id', $id)->first();
        
        
        if (!$oModel) {
            return $this->sendError('Not Found', ['Can\'t found '.$class.' With ID:'.$id], 404);
        }

        $sLang = $aData = $oRequest->input('lang');
        $oModel->setLang($sLang);

        $aErrors = [];
        $aUpdates = [];
        $aData = $oRequest->input('data');

        if (isset($aData['state']) && !is_bool($aData['state'])) {
            $aData['state'] = $aData['state'] === 'true';
        }

        $bCurState = $oModel->state;
        $oModel->updateData($aData);

        $msg = null;
        if ($aData['state'] != $oModel->state) {
            if ($bCurState) {
                if ($oModel->force_lang) {
                    $msg = 'Impossible de sauvegarder '.$oModel->userStr.' sans le désactiver. Avez-vous remplis toutes les informations de la langue ?';
                }
                else{
                    $msg = 'Impossible de sauvegarder '.$oModel->userStr.' sans le désactiver. Avez-vous remplis les informations dans toutes les langues ?';
                }
            }
            else{
                if ($oModel->force_lang) {
                    $msg = 'Impossible d\'activer '.$oModel->userStr.'. Avez-vous remplis toutes les informations de la langue ?';
                }
                else{
                    $msg = 'Impossible d\'activer '.$oModel->userStr.'. Avez-vous remplis les informations dans toutes les langues ?';
                }
            }
        }

        if (!$bCurState || empty($msg)) {
            if ($oModel->save()) {
                $oModel->setLang($sLang);
                return $this->sendResponse($oModel->toArray($this->bAdmin), $msg);
            }
        }

        if (empty($msg)) {
            $msg = 'Fail To Query Update';
        }

        return $this->sendError($msg, null, 200);
    }

    public function insert(Request $oRequest) {
        $class = $this->getClass();
        $aData = $oRequest->input('data');

        if (empty($aData['title'])) {
            return $this->sendError('Error: Missing '.$class.' title', ['Error: Missing '.$class.' Title'], 400);
        }

        $sLang = $oRequest->input('lang');
        if (!$sLang) {
            return $this->sendError('Fail To Query Insert', ['Lang Param is Requested'], 400);
        }

        $oModel = new $class;
        $oModel->setLang($sLang);
        
        if (isset($aData['state']) && !is_bool($aData['state'])) {
            $aData['state'] = $aData['state'] === 'true';
        }

        $oModel->updateData($aData);
        
        $msg = null;
        if ($aData['state'] != $oModel->state) {
            if ($oModel->force_lang) {
                $msg = 'Impossible d\'activer '.$oModel->userStr.'. Avez-vous remplis toutes les informations de la langue ?';
            }
            else{
                $msg = 'Impossible d\'activer '.$oModel->userStr.'. Avez-vous remplis les informations dans toutes les langues ?';
            }
        }

        if ($oModel->save()) {
            return $this->sendResponse($oModel->toArray($this->bAdmin), $msg);
        }

        return $this->sendError('Fail To Query Insert', null, 400);
    }

    public function delete(Request $oRequest) {
        $this->validate($oRequest, [
            'id' => 'required',
        ]);

        $id = $oRequest->input('id');

        $class = $this->getClass();
        $oModel = call_user_func($class.'::where', 'id', $id)->first();

        if ($oModel) {
            if ($oModel->delete()) {
                return $this->sendResponse(true, null);
            }

            return $this->sendError('Fail To Query Delete', ['Fail To Query Delete'], 400);
        }
        else{
            return $this->sendError('Fail To Query Delete', ['Fail To Find '.$class.' with ID: '.$id], 400);
        }
    }

    public function get($id, Request $oRequest) {
        if (!preg_match('/[a-z0-9]{8}-([a-z0-9]{4}-){3}[a-z0-9]{12}/', $id)) {
            return $this->sendError('Bad  ID', ['L\'identifiant est invalide'], 400);
        }

        $columns = $oRequest->input('columns');
        $sLang = $oRequest->input('lang');
        $class = $this->getClass();

        if (!$this->bAdmin) {
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

            /*if ($sLang) {
                $oModelList->where(function($query) use ($sLang){
                        $query->where('force_lang', $sLang)
                              ->orWhere('force_lang', '')
                              ->orWhereNull('force_lang')
                        ;
                    })
                ;
            }*/

            $oModel = $oModelList->get($columns)->first();
            if ($sLang) {
                $oModel->setLang($sLang);
            }
        }

        if (is_null($oModel)) {
            return $this->sendError('Not Found', ['Model introuvable'], 404);
        }

        return $this->sendResponse($oModel->toArray($this->bAdmin), null)->content();
    }

    public function getByCity($id, Request $oRequest) {
        $class = $this->getClass();
        $skip = $oRequest->getSkip();
        $limit = $oRequest->getLimit();
        $sLang = $oRequest->input('lang');
        $columns = $oRequest->input('columns');


        if (!$this->bAdmin) {
            $oModelList = ($class)::Where(['state', true]);
            $oModelList->take($limit);
        }
        else{
            $oModelList = call_user_func($class.'::take', $limit);
        }
        
        $oModelList = $oModelList->skip($skip)->get($columns);

        if ($sLang) {
            foreach ($oModelList as $key => &$oModel) {
                $oModel->setLang($sLang);
            }
        }
        

        return $this->sendResponse($oModel->toArray($this->bAdmin), null)->content();
    }

    public function byParcoursId(Request $oRequest=NULL, $id) {
        if (is_null($oRequest)) {
            $oRequest = new Request();
        }

        $sLang = $oRequest->input('lang');
        $columns = $oRequest->input('columns');

        $class = $this->getClass();
        $oModelList = ($class)::byParcours($id, $oRequest, $this->bAdmin);
        $oModelList = $oModelList->get($columns);

        if ($sLang) {
            $oModelList->setLang($sLang);
        }

        return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
        
        /*
            $where = [['parcours_id', '=', $id]];
            if (!$this->bAdmin) {
                $where[] = ['state', '=', 'true'];
            }

            $oModelList = $this->list($oRequest, true);
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
        */
    }

    public function byCityId(Request $oRequest=NULL, $id) {
        if (is_null($oRequest)) {
            $oRequest = new Request();
        }

        $skip = $oRequest->getSkip();
        $limit = $oRequest->getLimit();
        $sLang = $oRequest->input('lang');
        $columns = $oRequest->input('columns');

        $class = $this->getClass();
        $oModel = new $class;

        $where = [[$oModel->table.'.cities_id', '=', $id]];

        if (!$this->bAdmin) {
            $where[] = [$oModel->table.'.state', '=', 'true'];
        }

        $class = $this->getClass();

        $oModelList = ($class)::list($oRequest, $this->bAdmin, $where);
        $oModelList = $oModelList
            ->where($where)
            ->take($limit)
            ->skip($skip)
            ->get($columns)
        ;

        //var_dump($oModelList->toSql());
        /*var_dump($oModelList);
        exit;*/
        if ($sLang) {
            $oModelList->setLang($sLang);
        }

        return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
    }

    public function byNoParcours(Request $oRequest=NULL, $id) {
        if (is_null($oRequest)) {
            $oRequest = new Request();
        }

        $skip = $oRequest->getSkip();
        $limit = $oRequest->getLimit();
        $sLang = $oRequest->input('lang');
        $columns = $oRequest->input('columns');


        $bNoCity = $id === 'no-city';
        if (!$bNoCity) {
            $where = [['cities_id', '=', $id]];
        }
        else{
            $where = [];
        }

        if (!$this->bAdmin) {
            $where[] = ['state', '=', 'true'];
        }

        $oModelList = $this->list($oRequest, true);
        $oModelList = $oModelList
            ->where($where)
            ->whereNull('parcours_id')
        ;

        if ($bNoCity) {
            $oModelList->whereNull('cities_id');
        }

        $oModelList = $oModelList->take($limit)
            ->skip($skip)
            ->get($columns)
        ;


        if ($sLang) {
            $oModelList->setLang($sLang);
        }

        return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
    }

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

        foreach ($columns as &$sCol) {
            $sCol = $table.'.'.$sCol;
        }


        $oModelList = $this->list($oRequest, true)
            ->leftJoin('cities', $table.'.cities_id', '=', 'cities.id')
            ->where(function($query) {
            $query->whereNull('cities_id')
                  ->orWhereNull('cities.id')
            ;
        });

        if (!$this->bAdmin) {
            $oModelList->where(['state', '=', 'true']);
        }

        $oModelList = $oModelList
            ->take($limit)
            ->skip($skip)
            ->get($columns)
        ;

        /*var_dump($oModelList->toSql());
        $oModelList = $oModelList->get($columns);
        var_dump($oModelList);*/


        if ($sLang) {
            $oModelList->setLang($sLang);
        }

        return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
    }

    public function search(Request $oRequest) {
        $class = $this->getClass();
        $sLang = $oRequest->input('lang');
        $order = $oRequest->input('order');
        $search = $oRequest->input('search');
        $parents = $oRequest->input('parents');
        $columns = $oRequest->input('columns');
        
        //var_dump($columns);

        $oModelList = ($class)::search($search, $columns, $order);
        //$oModelList = $oModelList->get(['id']);


        //$oModelList = $oModelList->get($columns);

        if ($sLang) {
            $oModelList->setLang($sLang);
        }


        if ($parents) {
            $oModelList->loadParents();
        }

        if ($parents) {
            $oModelList->loadParents();
        }

        return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
    }
}
