<?php

namespace App\Http\Controllers;

use App\Utils\APIControllerUtil as Controller;
use App\Utils\RequestUtil as Request;
use App\Utils\ValidatorUtil as Validator;
use App\Models\Users;

class ColorsController extends Controller
{
    protected $sModelClass = 'Colors';

    public function list(Request $oRequest, $bAsList=false) {
        $skip = $oRequest->getSkip();
        $limit = $oRequest->getLimit();
        $sLang = $oRequest->input('lang');
        $aOrder = $oRequest->getOrder();

        $columns = $oRequest->input('columns');

        $class = $this->getClass();

        $oModelList = ($class)::Take($limit)->skip($skip);

        if ($aOrder && count($aOrder) == 2) {
            $sOrderCol = $aOrder[0];
            $sOrderWay = $aOrder[1];
            
            $oModelList->orderBy($sOrderCol, $sOrderWay);
        }
        else {
            $oModelList->orderBy('name', 'ASC');
        }

        $oModelList = $oModelList->get($columns);

        return $this->sendResponse($oModelList->toArray(), null)->content();
    }
}
