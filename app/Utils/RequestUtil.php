<?php

namespace App\Utils;

use Illuminate\Http\Request;

class RequestUtil extends Request
{
   public static $sLang = null;
   public static $dLimit = null;
   public static $dSkip = null;

   public function __construct () {
       parent::__construct();
       $this->__init();
   }

   public function __init () {
       // ==> Set lang.
       if ($this->getParam('lang')) {
           static::$sLang = strtolower($this->getParam('lang'));
       }
       // ==> Set limit.
       if (is_numeric($this->getParam('limit'))) {
           static::$dLimit = intval($this->getParam('limit'));
       }
       // ==> Set limit.
       if (is_numeric($this->getParam('skip'))) {
           static::$dSkip = intval($this->getParam('skip'));
       }
   }

   public function getParam($sKey) {
       return empty($_GET[$sKey]) ? null : $_GET[$sKey];
   }

   public static function getParams() {
       return [
           'lang' => static::$sLang,
           'limit' => static::$dLimit,
           'skip' => static::$dSkip
       ];
   }

   public function getLang () {
       return static::$sLang;
   }

   public function getlimit () {
       return static::$dLimit;
   }

   public function getSkip () {
       return static::$dSkip;
   }
}