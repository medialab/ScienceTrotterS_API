<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;

class ModelUtil extends Model
{

    /**
     *
     */
    public function __get($sVar) {
      if (array_key_exists($sVar, $this->getCasts()) && $this->getCasts()[$sVar] === 'json') {
        if (empty($this->attributes[$sVar])) {
          $this->attributes[$sVar] = new \StdClass;
        }
      } else {
        if (empty($this->attributes[$sVar])) {
          $this->attributes[$sVar] = null;
        }
      }

      return $this->attributes[$sVar];
    }

    public function __set($sKey, $sValue) {
      $this->attributes[$sKey] = $sValue;
    }

    public function save(Array $otps = []) {
      foreach ($this->attributes as $iKey => $iAttr) {
         if (array_key_exists($iKey, $this->getCasts()) && $this->getCasts()[$iKey] === 'json') {
          $this->attributes[$iKey] = json_encode($iAttr);
         }
      }
      return Parent::save($otps);
    }

}
