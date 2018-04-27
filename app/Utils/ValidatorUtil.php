<?php
namespace App\Utils;

use Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Utils\APIControllerUtil;

class ValidatorUtil extends Validator 
{
    public static function validateOrError($oResponse, $oRequest, $aPatterns) 
    {
        $oValidator = self::make($oRequest, $aPatterns);

        if ($oValidator->fails()) {
            throw new HttpResponseException(
                $oResponse->sendError(null, $oValidator->errors())
            );
        }
        return true;
    }
}
