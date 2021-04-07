<?php

namespace Drupal\lol_integration\Classes;

class GeneralData{
  public $language = 'en_US';
  public $version = '10.12.1';


  public function baseIconImageUrl(){
    return "http://ddragon.leagueoflegends.com/cdn/".$this->version."/img/champion/";
  }

}
