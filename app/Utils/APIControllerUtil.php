<?php

namespace App\Utils;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Utils\RequestUtil as Request;

class APIControllerUtil extends BaseController
{
    protected $sModelClass;

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
        $class = 'App\Models\\'.$this->sModelClass;
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
}
