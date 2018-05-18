<?php

namespace App\Utils;

use Laravel\Lumen\Routing\Controller as BaseController;

class APIControllerUtil extends BaseController
{
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
