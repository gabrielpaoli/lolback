<?php

namespace Drupal\lol_integration\Controller;
use Drupal;
use Drupal\Core\Controller\ControllerBase;
use \Drupal\lol_integration\Classes\GeneralData;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChampionDataController extends ControllerBase {

  public function getGeneralData(){
    $generalData = new GeneralData();
    return $generalData;
  }

  public function getData($id = ''){
    $sanitizedId = '';
    $prefix = '';
    if(!empty($id)){
      $sanitizedId = '_'.$id;
      $prefix = '/';
    }

    $cid = 'lol_integration_get_list'.$sanitizedId.':'. Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
    $data = NULL;

    if ($cache = Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {
      $url = 'http://ddragon.leagueoflegends.com/cdn/'.$this->getGeneralData()->version.'/data/'.$this->getGeneralData()->language;
      $response = lol_integration_reponse($url.'/champion'.$prefix.$id.'.json', 'GET');
      $data = json_decode($response);
      Drupal::cache()->set($cid, $data);
    }
    return $data;
  }

  public function getList(){

    $data = $this->getData();
    $data = $this->orderData($data->data);

    $queryParameterChampion = Drupal::request()->query->get('champion');
    $queryParameterTag = Drupal::request()->query->get('tag');

    if(!empty($queryParameterChampion))
      $data = $this->array_search_partial_champion($data, $queryParameterChampion);

    if(!empty($queryParameterTag))
      $data = $this->array_search_tag($data, $queryParameterTag);

    return new JsonResponse($data);
  }

  public function getListPerId(){
    $data = $this->getData();
    $data = $this->orderDataPerId($data->data);
    return new JsonResponse($data);
  }

  public function getChampionPerId(){
    $id = \Drupal::routeMatch()->getParameter('id');
    $data = $this->getData($id)->data->{$id};
    $version = $this->getData($id)->version;
    $data = (array)json_decode(json_encode($data), true);
    $data['version'] = $version;
    return new JsonResponse($data);
  }

  private function orderData($data){
    $fData = [];
    foreach($data as $dataInn){
      $img = "http://ddragon.leagueoflegends.com/cdn/".$this->getGeneralData()->version."/img/champion/" . $dataInn->image->full;
      $fData[$dataInn->id] = array(
        'id' => $dataInn->id,
        'name' => $dataInn->name,
        'imgSrc' => $img,
        'tags' => $dataInn->tags,
        'key' => $dataInn->key,
      );
    }
    return $fData;
  }

  private function orderDataPerId($data){
    $fData = [];
    foreach($data as $dataInn){
      $img = "http://ddragon.leagueoflegends.com/cdn/".$this->getGeneralData()->version."/img/champion/" . $dataInn->image->full;
      $fData[$dataInn->key] = array(
        'id' => $dataInn->id,
        'name' => $dataInn->name,
        'imgSrc' => $img,
        'key' => $dataInn->key,
      );
    }
    return $fData;
  }

  function array_search_partial_champion($arr, $keyword) {
    $accumulate = [];
    foreach($arr as $index => $string) {
      if (stripos($string["name"], $keyword) !== FALSE){
        $accumulate[$index] = $string;
      }
    }
    return $accumulate;
  }

  function array_search_tag($arr, $keyword){
    $accumulate = [];
    foreach($arr as $index => $content){
      if (in_array($keyword, $content["tags"])){
        $accumulate[$index] = $content;
      }
    }
    return $accumulate;
  }


}
