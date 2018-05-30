<?php

namespace App\Utils;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Utils\RequestUtil as Request;

class APIControllerUtil extends BaseController
{
    protected $sModelClass;

    public function getClass() {
        return 'App\Models\\'.$this->sModelClass;
    }

    public function list(Request $oRequest) {
        $limit = (int)$oRequest->input('limit');
        if (!$limit) {
            $limit = 15;
        }
        
        $skip = (int)$oRequest->input('skip');
        if (!$skip) {
            $skip = false;
        }

        $sLang = $oRequest->input('lang');
        $class = $this->getClass();
        if ($sLang) {

            $oModelList = call_user_func($class.'::where', 'state->'.$sLang, 'true');
            $oModelList = $oModelList->take($limit)->skip($skip)->get();

            foreach ($oModelList as $key => &$oModel) {
                $oModel->setLang($sLang);
            }
        }
        else{
            $oModelList = call_user_func($class.'::take', $limit);
            $oModelList = $oModelList->skip($skip)->get();
        }

        return $this->sendResponse($oModelList->toArray(), null)->content();
    }

    public function sendResponse($result, $message)
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
        /* On crée le dossier de l'image */
        $dir = dirname(UPLOAD_PATH.$sName);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        /* Url De téléchargement de l'image */
        $imgUrl = ADMIN_URL.'upload/'.$sName;

        /* si l'image existe on la remplace */
        $sPath = UPLOAD_PATH.$sName;
        if (file_exists($sPath)) {
            unlink($sPath);
        }

        file_put_contents($sPath, fopen($imgUrl, 'r'));
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

        foreach ($aData as $key => $value) {
            
            /* Données à Ignorer lors de l'update */
            if (in_array($key, ['id', 'created_at', 'updated_at'])) {
                continue;
            }
            elseif ($key === 'image' && empty($value)) {
                continue;
            }


            $oModel->$key = $value;
        }
        
        if(!empty($aUpdates['image']) && $aUpdates['image'] !== $oModel->image) {
            $this->downloadImage($aUpdates['image']);
        }

        /* La ville ne peut être activée que si tout les champs sont remplis */
        if (!strlen($oModel->image) || !strlen($oModel->geoloc)) {

            $oModel->state = false;
        }

        if ($oModel->save()) {
            return $this->sendResponse($oModel, null);
        }

        return $this->sendError('Fail To Query Update', ['Fail To Query Update'], 400);
    }

    public function insert(Request $oRequest) {
        $class = $this->getClass();
        $aData = $oRequest->input('data');

        if (empty($aData['label'])) {
            return $this->sendError('Error: Missing '.$class.' Label', ['Error: Missing '.$class.' Label'], 400);
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
            elseif ($key === 'image' && empty($value)) {
                continue;
            }

            $oModel->$key = $value;
        }

        if (!empty($aData['image'])) {
            $this->downloadImage($aData['image']);
            $oModel->image = $aData['image'];
        }

        /* La ville ne peut être activée que si tout les champs sont remplis */
        if (!strlen($oModel->image) || !strlen($oModel->geoloc)) {
            $oModel->state = false;
        }

        if ($oModel->save()) {
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

    public function get($id) {
        $class = $this->getClass();
        $oModel = call_user_func($class.'::where', 'id', $id)->first();
        
        return $this->sendResponse($oModel->toArray(), null)->content();
    }
}
