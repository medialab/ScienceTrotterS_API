<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustModel extends Model
{
    private $sCurLang = false;
    protected $aTranslateVars = [];

    function __get($sVar) {
        var_dump("Variable: ".$sVar);
        var_dump("Attrs: ", $this->attributes);

        if (array_key_exists($sVar, $this->attributes)) {
            var_dump("SQL VAR");
            if (in_array($sVar, $this->aTranslateVars)) {
                var_dump("TRANSLATE VAR");
                
                $var = $this->attributes[$sVar];
                var_dump("Cur Value: ", $var);

                if (is_string($var)) {
                    var_dump("Décoding Cur Value");
                    $var = json_decode($var);
                    if (is_null($var)) {
                        var_dump("Fail To Décode");
                        return null;
                    }

                    var_dump("Décoded: ", $var);
                    $this->attributes[$sVar] = $var;
                }

                if ($this->sCurLang) {
                    $sLang = $this->sCurLang;
                    return empty($var->$sLang) ? null : $var->$sLang;
                }

                return $var;
            }
        }
        
        return empty($this->$sVar) ? null : $this->$sVar;
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
