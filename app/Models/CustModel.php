<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustModel extends Model
{
    private $sCurLang = false;
    protected $aTranslateVars = [];

    function __get($sVar) {
        if (in_array($sVar, $this->aTranslateVars)) {
            $var = $this->$sVar;

            if (is_string($var)) {
                $var = json_decode($var);
                if (!is_null($var)) {
                    return null;
                }

                $this->$sVar = $var;
            }

            if ($this->sCurLang) {
                $sLang = $this->sCurLang;
                return empty($var->$sLang) ? null : $var->$sLang;
            }

            return $var;
        }
        else{
            return empty($this->$sVar) ? null : $this->$sVar;
        }
    }

    private function setValueByLang($sVar, $value) {
        $sLang = $this->sCurLang;
        $var = $this->$sVar;
        
        if (empty($value)) {
            $value = '';
        }
        elseif (!is_string($value)) {            
            throw new Exception("Error: Can't Set Parcours::{$sVar} for Language: '{$sLang}'. Data must be string", 1);
        }

        if(is_string($var)) {
            $var = json_decode($var);
        }

        if (empty($var)) {
            $var = new StdClass;
        }

        $var->$sLang = $value;
        $this->$sVar = $var;
    }

    private function setValueAsJson($sVar, $value) {        
        if (empty($value)) {
            $var = new StdClass;
        }
        elseif (is_string($value)) {            
            $var = json_decode($value);
            
            if (is_null($var)) {
                throw new Exception("Error: Can't Set Parcours::$sVar Due to Invalid Json: '$value'", 1);
            }
        }
        elseif (is_array($value)) {
            $var = (object) $value;
        }
        elseif(!is_object($value)) {
            throw new Exception("Error: Can't Set Parcours::$sVar Due to Invalid Data Type. Accepted StdClass, Array, String (json) OR null", 1);
        }

        $this->$sVar = $var;
    }

    function __set($sVar, $value) {
        if (in_array($sVar, $this->aTranslateVars)) {
            $var = $this->$sVar;
            
            if ($this->sCurLang) {
                $this->setValueByLang($sVar, $value);
            }
            else{
                $this->setValueAsJson($sVar, $value);
            }
        }
        else{
            $this->$sVar = $value;
        }
    }

    public function setLang($l = false) {
        $this->sCurLang = $l;
    }
}
