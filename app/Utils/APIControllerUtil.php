<?php

namespace App\Utils;

use Laravel\Lumen\Routing\Controller as BaseController;

class APIControllerUtil extends BaseController
{
    public function sendResponse($result, $message)
    {
        $aResponse = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($aResponse, 200);
    }
    public function sendError($error, $errorMessages = [], $dCode = 400)
    {
        $aResponse = [
            'success' => false,
            'message' => $error,
        ];
        if (! empty($errorMessages)) {
            $aResponse['data'] = $errorMessages;
        }
        return response()->json($aResponse, $dCode);
    }
}
