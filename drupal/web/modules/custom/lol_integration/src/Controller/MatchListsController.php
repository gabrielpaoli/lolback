<?php

namespace Drupal\lol_integration\Controller;
use Drupal;
use Drupal\Core\Controller\ControllerBase;
use \Drupal\lol_integration\Classes\GeneralData;
use Symfony\Component\HttpFoundation\JsonResponse;



class MatchListsController extends ControllerBase {

  public function getMatchlistsPerChampionAccoundId(){
    //M4SGcaqUu5DGqM7QcIYlcGNbdYldTC5LNBtt1m-vbc8
    $queryParameterBeginIndex = Drupal::request()->query->get('beginIndex');
    $position = Drupal::request()->query->get('position');
    $encryptedAccountId = \Drupal::routeMatch()->getParameter('encryptedAccountId');

    if(empty($queryParameterBeginIndex))
      $queryParameterBeginIndex = 0;

    if(empty($position))
      $position = 0;

    $cid = 'lol_integration_get_match_summoner_'.$encryptedAccountId .'_'.strtolower($queryParameterBeginIndex). '_' .$position.':'. Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
    $data = NULL;

    if ($cache = Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }else{
      $url = 'https://la2.api.riotgames.com/lol/match/v4/matchlists/by-account/'.$encryptedAccountId . '?beginIndex='.$queryParameterBeginIndex;
      $response = lol_integration_reponse($url, 'GET');
      $response = json_decode($response)->matches;

      $gamesSummonerData = array_chunk($response, 10);

      $gamesIdF = array_column($gamesSummonerData[$position], 'gameId');

      foreach($gamesIdF as $key => $gameId){
        $url = 'https://la2.api.riotgames.com/lol/match/v4/matches/'.$gameId;
        $responseGI = lol_integration_reponse($url, 'GET');
        $generalData = json_decode($responseGI);
        $data[] = [
          'generalData' => $generalData,
          'summonerData' => $this->orderSummonerData($gamesSummonerData[$position][$key], $generalData),
          'gameData' => $gamesSummonerData[$position][$key],
        ];
      }

      Drupal::cache()->set($cid, $data);

    }

    return new JsonResponse($data);
  }

  private function orderSummonerData($summoner, $generalData){
    $championId = $summoner->champion;
    foreach($generalData->participants as $key => $indData){
      if($indData->championId == $championId){
        return $indData;
      }
    }
  }


}
