<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustModel extends Model
{
    private $sCurLang = false; // Langue Séléctionnée
    protected $aTranslateVars = []; // les Variables à traduire


    function __get($sVar) {
        // Si il s'agit d'une variable De la BDD
        if (array_key_exists($sVar, $this->attributes)) {

            // Si il s'agit d'une variable à traduire
            if (in_array($sVar, $this->aTranslateVars)) {
                if (empty($this->attributes[$sVar])) {
                    $var = new \StdClass;
                }
                else{
                    $var = $this->attributes[$sVar];
                }

                // Si la valeur actuelle est une string on la décode
                if (is_string($var)) {
                    $var = json_decode($var);

                    // En cas d'échec on retourne NULL
                    if (is_null($var)) {
                        return null;
                    }

                    $this->attributes[$sVar] = $var;
                }

                // Si une langue est séléctionnée
                if ($this->sCurLang) {
                    $sLang = $this->sCurLang;
                    return empty($var->$sLang) ? null : $var->$sLang;
                }

                return $var;
            }
        }
        return empty($this->$sVar) ? null : $this->$sVar;
    }

    /**
     * Mets à jour une variable traductible pour une langue
     * @param String $sVar  Nom de la variable
     * @param mixed $value valeur de la variable
     */
    private function setValueByLang($sVar, $value) {
        var_dump("SETTING VALUE: $sVar", $value);
        $sLang = $this->sCurLang;
        
        if ($value !== false && empty($value)) {
            $value = null;
        }
        var_dump("new Value", $value);
        
        if (empty($this->attributes[$sVar])) {
            var_dump("Current Is Empty");
            $var = new \StdClass;
        }
        else{
            $var = $this->attributes[$sVar];
        }

        // Si la valeur actuelle est une string on la décode
        if(is_string($var)) {
            var_dump("Décoding Current");
            $var = json_decode($var);
        }

        var_dump("Current Value", $var);

        $var->$sLang = $value;
        
        var_dump("Updated Value", $var);
        $this->attributes[$sVar] = $var;
    }

    /**
     * Mets à jour une variable traductible pour toutes les langues
     * @param String $sVar  Nom de la variable
     * @param mixed $value Valeur de la variable
     */
    private function setValueAsJson($sVar, $value) {
        if (empty($this->attributes[$sVar])) {
            $var = new \StdClass;
        }
        else{
            $var = $this->attributes[$sVar];
        }

        if (empty($value)) {
            $var = null;
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

        $this->attributes[$sVar] = $var;
    }

    function __set($sVar, $value) {
        // Si il s'agit d'une variable à traduire
        if (in_array($sVar, $this->aTranslateVars)) {
            $var = $this->$sVar;
            
            // Si une langue est choisie on met à jour que celle ci
            if ($this->sCurLang) {
                $this->setValueByLang($sVar, $value);
            }
            // Si aucune langue est choisie on les met toutes à jour
            else{
                $this->setValueAsJson($sVar, $value);
            }
        }
        elseif(in_array($sVar, $this->attributes) || in_array($sVar, $this->fillable)){
            $this->attributes[$sVar] = $value;
        }
        elseif(property_exists($this, $sVar)){
            $this->$sVar = $value;
        }
        else{
            throw new \Exception("Error: Try To Set $sVar In Model", 1);
        }
    }

    // Définis la langue de l'objet
    public function setLang($l = false) {
        $this->sCurLang = $l;
    }

    /**
     * Override de la fonction afin d'implémenter les traductions
     * @return Array données de l'objet
     */
    public function toArray() {
        $aResult = [];
        $sLang = $this->sCurLang;

        foreach ($this->attributes as $sVar => $value) {

            if (in_array($sVar, $this->hidden)) {
                continue;
            }

            if ($sLang && in_array($sVar, $this->aTranslateVars)) {
                if (is_string($value)) {
                    $value = json_decode($value);
                }

                if (empty($value) || (empty($value->$sLang) && $value->$sLang !== false)) {
                    $value = null;
                }
                else{
                    $aResult[$sVar] = $value->$sLang;
                }
            }
            else{
                $aResult[$sVar] = $value;
            }
        }

        $aResult['sCurLang'] = $sLang;
        return $aResult;
    }

    public function save(Array $options=[]) {
        foreach ($this->aTranslateVars as $sVar) {
            $value = &$this->attributes[$sVar];
            
            if (!is_string($value)) {
                $value = json_encode($value);
            }
        }
    
        return Parent::save($options);
    }
}
