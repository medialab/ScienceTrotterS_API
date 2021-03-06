<?php

namespace Database\Mockup;

use App\Models\Users;
use App\Models\Cities;
use App\Models\Colors;
use App\Models\Parcours;
use App\Models\Interests;

class DefaultMockup 
{
  public function getFile($sTable) {
    $sFile = file_get_contents(__DIR__ . '/json/' . $sTable . '.json');
    return json_decode($sFile);
  }

  public function init() {
    /*$this->table_cities();
    $this->table_parcours();
    $this->table_interests();*/
    $this->table_users();
    $this->table_colors();
  }

  public function table_cities() {
    $aData = $this->getFile('cities');

    Cities::truncate();

    foreach ($aData as $iData) {
      $oCity = new Cities;

      if (isset($iData->id)) { $oCity->id = $iData->id; }

      $oCity->title = $iData->title;
      $oCity->geoloc = $iData->geoloc;
      $oCity->image = $iData->image;
      $oCity->state = $iData->state;
      $oCity->force_lang = $iData->force_lang;
      $oCity->save();
    }
  }

  public function table_parcours() {
    $aData = $this->getFile('parcours');

    Parcours::truncate();

    foreach ($aData as $iData) {
      $oParcour = new Parcours;

      if (isset($iData->id)) { $oParcour->id = $iData->id; }
      if (isset($iData->cities_id)) { $oParcour->cities_id = $iData->cities_id; }

      $oParcour->title = $iData->title;
      $oParcour->time = $iData->time;
      $oParcour->audio = $iData->audio;
      $oParcour->color = $iData->color;
      $oParcour->description = $iData->description;
      $oParcour->audio_script = null;
      $oParcour->state = $iData->state;
      $oParcour->force_lang = $iData->force_lang;
      $oParcour->save();
    }
  }

  public function table_interests() {
    $aData = $this->getFile('interests');

    Interests::truncate();

    foreach ($aData as $iData) {
      $oInterest = new Interests;

      if (isset($iData->id)) { $oInterest->id = $iData->id; }
      if (isset($iData->cities_id)) { $oInterest->cities_id = $iData->cities_id; }
      if (isset($iData->parcours_id)) { $oInterest->parcours_id = $iData->parcours_id; }

      $oInterest->header_image = $iData->header_image;
      $oInterest->title = $iData->title;
      $oInterest->address = $iData->address;
      $oInterest->geoloc = $iData->geoloc;
      $oInterest->schedule = $iData->schedule;
      $oInterest->price = $iData->price;
      $oInterest->audio = $iData->audio;
      $oInterest->transport = $iData->transport;
      $oInterest->description = null;
      $oInterest->audio_script = $iData->audio_script;
      $oInterest->gallery_image = $iData->gallery_image;
      $oInterest->bibliography = $iData->bibliography;
      $oInterest->force_lang = $iData->force_lang;
      $oInterest->state = $iData->state;
      $oInterest->save();
    }
  }

  public function table_users() {
    $aData = $this->getFile('users');

    Users::truncate();

    foreach ($aData as $iData) {
      $oUsers = new Users;

      foreach ($iData as $sKey => $sData) {
        $oUsers->$sKey = $sData;
      }

      $oUsers->save();
    }
  }

  public function table_colors() {
    $aData = $this->getFile('colors');

    Colors::truncate();

    foreach ($aData as $iData) {
      $oUsers = new Colors;

      foreach ($iData as $sKey => $sData) {
        $oUsers->$sKey = $sData;
      }

      $oUsers->save();
    }
  }
}









