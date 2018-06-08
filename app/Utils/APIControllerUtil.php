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

    public function list(Request $oRequest) {
        $skip = $oRequest->getSkip();
        $limit = $oRequest->getLimit();
        $sLang = $oRequest->input('lang');

        $columns = $oRequest->input('columns');

        $class = $this->getClass();

        if (!$this->bAdmin) {
            $oModelList = 
                ($class)::Where('state', true)->where(function($query) use ($sLang){
                    $query->where('force_lang', $sLang)
                          ->orWhere('force_lang', '')
                          ->orWhereNull('force_lang')
                    ;
                })
                ->take($limit)
                ->skip($skip)
                ->get($columns)
            ;
        }
        else{
            $oModelList = call_user_func($class.'::take', $limit);

            if ($sLang) {
                $oModelList
                    ->where('force_lang', $sLang)
                    ->orWhere('force_lang', '')
                    ->orWhereNull('force_lang')
                 //   ->take($limit)->skip($skip)
                ;
            }

            $oModelList = $oModelList->take($limit)->skip($skip)->get($columns);
        }

        if ($sLang) {
            foreach ($oModelList as $key => &$oModel) {
                $oModel->setLang($sLang);
            }
        }

        ModelUtil::$bAdmin = $this->bAdmin;
        return $this->sendResponse($oModelList->toArray($this->bAdmin), null)->content();
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


    /**
     * Télécharge l'image de la ville depuis le serveur d'Admin
     * @param  String $sName Nom de l'image
     */
    private function downloadImage($sName) {
        //var_dump("Downloading", $sName);
        /* On crée le dossier de l'image */
        $dir = dirname(UPLOAD_PATH.$sName);
        if (!is_dir($dir)) {
            //var_dump("Dir Not Exists");
            mkdir($dir, 0775, true);
        }

        /* Url De téléchargement de l'image */
        $imgUrl = ADMIN_URL.'upload/'.$sName;
        //var_dump("url", $imgUrl);

        /* si l'image existe on la remplace */
        $sPath = UPLOAD_PATH.$sName;
        //var_dump("path", $sPath);
        //var_dump("path", realpath($sPath));

        
        if (file_exists($sPath)) {
            //var_dump("Delete Existing");
            unlink($sPath);
        }

        $b = file_put_contents($sPath, fopen($imgUrl, 'r'));
        //var_dump("Result", $b);
        //exit;
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

        //var_dump("Adatas", $aData);
        foreach ($aData as $key => $value) {
            //var_dump("===Key: $key");
            //var_dump(strpos($key, 'image'));
            
            /* Données à Ignorer lors de l'update */
            if (in_array($key, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            elseif (strpos($key, 'image') !== false) {
                //var_dump("Image $key", $value);
                if (empty($value)) {
                    //var_dump("Image Empty");
                    continue;
                }
                
                //var_dump("TEST", !empty($value), $value !== $oModel->$key);
                if(!empty($value) && $value !== $oModel->$key) {
                    //var_dump("downloading");
                    $this->downloadImage($value);
                }
            }


            $oModel->$key = $value;
        }

        if (property_exists($oModel, 'geoloc')) {
            /* La ville ne peut être activée que si tout les champs sont remplis */
            if (!strlen($oModel->image) || is_null($oCity->geoloc) || count(get_object_vars($oModel->geoloc)) != 2) {
                $oModel->state = false;
            }
        }

        if ($oModel->save()) {
            ModelUtil::$bAdmin = true;
            return $this->sendResponse($oModel, null);
        }

        return $this->sendError('Fail To Query Update', ['Fail To Query Update'], 400);
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
        foreach ($aData as $key => $value) {
            /* Données à Ignorer lors de l'update */
            if (in_array($key, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            if (empty($value)) {
                if (strpos($key, 'image') !== false) {
                    continue;
                }
                elseif(strpos($key, 'id')) {
                    continue;
                }
            }
            elseif (strpos($key, 'image') !== false) {
                if(!empty($aUpdates[$key]) && $aUpdates[$key] !== $oModel->image) {
                    $this->downloadImage($aUpdates[$key]);
                }
            }

            $oModel->$key = $value;
        }

        if (!empty($aData['image'])) {
            $this->downloadImage($aData['image']);
            $oModel->image = $aData['image'];
        }

        /* La ville ne peut être activée que si tout les champs sont remplis */
        if (!strlen($oModel->image) || is_null($oCity->geoloc) || count(get_object_vars($oModel->geoloc)) != 2) {
            $oModel->state = false;
        }

        if ($oModel->save()) {
            ModelUtil::$bAdmin = true;
            return $this->sendResponse($oModel, null);
        }

        return $this->sendError('Fail To Query Insert', ['Fail To Query Insert'], 400);
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

            if ($sLang) {
                $oModelList->where(function($query) use ($sLang){
                        $query->where('force_lang', $sLang)
                              ->orWhere('force_lang', '')
                              ->orWhereNull('force_lang')
                        ;
                    })
                ;
            }

            $oModel = $oModelList->get($columns)->first();
            if ($sLang) {
                $oModel->setLang($sLang);
            }
        }

        ModelUtil::$bAdmin = $this->bAdmin;
        if (is_null($oModel)) {
            return $this->sendError('Not Found', ['Model introuvable'], 404);
        }

        return $this->sendResponse($oModel->toArray($this->bAdmin), null)->content();
    }

    public function getByCity($id, Request $oRequest) {
        $columns = $oRequest->input('columns');

        if (!$this->bAdmin) {
            $oModelList = call_user_func($class.'::where', 'state', 'true');
            $oModelList = $oModelList->take($limit)->skip($skip)->get($columns);
        }
        else{
            $oModelList = call_user_func($class.'::take', $limit);
            $oModelList = $oModelList->skip($skip)->get($columns);
        }

        if ($sLang) {
            foreach ($oModelList as $key => &$oModel) {
                $oModel->setLang($sLang);
            }
        }
        

        return $this->sendResponse($oModel->toArray(), null)->content();
    }
}
