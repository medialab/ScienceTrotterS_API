<?php

namespace App\Utils;

class CheckerUtil
{

  public static function is_uuid_v4($sUUID) {
    $sPattern = '~^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i';
    return preg_match($sPattern, $sUUID) > 0;
  }

}




