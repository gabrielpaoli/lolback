<?php

namespace Drupal\lol_integration\Controller;
use Drupal;
use Drupal\Core\Controller\ControllerBase;
use \Drupal\lol_integration\Classes\GeneralData;
use Symfony\Component\HttpFoundation\JsonResponse;



class SummonerController extends ControllerBase {

  public function getSummonerPerName($region = '', $summonerName = ''){

    $cid = 'lol_integration_summoner_get_'.$region. '_' .$summonerName.':'. Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
    $data = NULL;

    if ($cache = Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {
      $url = 'https://'.$region.'.api.riotgames.com/lol/summoner/v4/summoners/by-name/'.$summonerName;
      $response = lol_integration_reponse($url, 'GET');
      $data = json_decode($response);
      $data->region = $region;
      Drupal::cache()->set($cid, $data);
    }

    return new JsonResponse($data);
  }

  public function getSummonerPerId($region = '', $encryptedAccountId = ''){

    $cid = 'lol_integration_summoner_get_per_id_'.$region. '_' .$encryptedAccountId . ':'. Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
    $data = NULL;

    if ($cache = Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {

      $urlSummoner = 'https://'.$region.'.api.riotgames.com/lol/summoner/v4/summoners/by-account/'. $encryptedAccountId;
      $responseSummoner = lol_integration_reponse($urlSummoner, 'GET');
      $summonerGeneralInfo = json_decode($responseSummoner);

      $urlLeagueData = 'https://'.$region.'.api.riotgames.com/lol/league/v4/entries/by-summoner/'. $summonerGeneralInfo->id;
      $responseLeague = lol_integration_reponse($urlLeagueData, 'GET');
      $league = json_decode($responseLeague);
      $baseUrl = 'http://lolstatics.test/sites/default/files/';

      $data = [
        'generalInfo' => $summonerGeneralInfo,
        'league' => $league,
        'baseUrl' => $baseUrl
      ];

      Drupal::cache()->set($cid, $data);
    }

    return new JsonResponse($data);
  }

  public function getChampionMastery($region = '', $encryptedAccountId = ''){

    $cid = 'lol_integration_champion_mastery_'.$region . '_' .$encryptedAccountId . ':'. Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
    $data = NULL;

    if ($cache = Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {
      $urlSummoner = 'https://'.$region.'.api.riotgames.com/lol/summoner/v4/summoners/by-account/'. $encryptedAccountId;
      $responseSummoner = lol_integration_reponse($urlSummoner, 'GET');
      $summonerGeneralInfo = json_decode($responseSummoner);

      $url = 'https://'.$region.'.api.riotgames.com/lol/champion-mastery/v4/champion-masteries/by-summoner/' . $summonerGeneralInfo->id;
      $response = lol_integration_reponse($url, 'GET');
      $data = json_decode($response);
      Drupal::cache()->set($cid, $data);
    }

    return new JsonResponse($data);
  }


}
