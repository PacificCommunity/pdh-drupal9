<?php

namespace Drupal\spc_publication_import;

/**
 * Description of spcPublicationImport
 *
 * @author shchedrin
 */
class spcPublicationImport {
  
  /**
   * {@inheritdoc}
   */
  protected $httpClient;
  
  /**
   * {@inheritdoc}
   */
  protected $base_url;
  
  /**
   * {@inheritdoc}
   */
  protected $requested_path;
  
  /**
   * {@inheritdoc}
   */
  protected $request_url;
  
  /**
   * {@inheritdoc}
   */
  protected $method;
  
  /**
   * {@inheritdoc}
   */
  protected $params;
  
  /**
   * {@inheritdoc}
   */
  protected $rows;  

  /**
   * {@inheritdoc}
   */
  public function __construct(){
    
    $this->base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
    $this->requested_path = 'api/3/action/package_search';
    $this->rows = '1000';
    $this->method = 'GET';
    $this->httpClient = \Drupal::httpClient();
  }
  
  /**
   * {@inheritdoc}
   */
  private function request() {
    
    try {
      $request = $this->httpClient->request($this->method, $this->request_url);
      $response = json_decode($request->getBody()->getContents());
      
      return $response;      

    } catch (RequestException $e) {
      watchdog_exception('spc_pbank', $e->getMessage());
    }
  }
  
  /**
   * {@inheritdoc}
   */
  private function getParams($args){
    
    $get_params['types'] = [
     'publications' 
    ];
    
    $q = '?q=';
    
    if(!empty($get_params['types'])){
      foreach($get_params['types'] as $type){
        $q .= 'type:' . $type . rawurlencode(' ');
      }
    }
    
    $get_params['tags'] = [
     'policy',
     'education' 
    ];
    
    $numItems = count($get_params['tags']);
    $i = 0;
    if(!empty($get_params['tags'])){
      $tags = '';
      foreach($get_params['tags'] as $tag){
        $tags .= 'tags:' . $tag ;
        if(++$i != $numItems) {
          $tags .= rawurlencode(' ') . 'AND' . rawurlencode(' ');
        }
      }
    }
    
    $q .= '(' . $tags . ')';
    $q .= '&rows=' . $this->rows;
    
    return $q;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPublications($args = null) {
    
    $this->params = $this->getParams($args);
    $this->request_url = $this->base_url . $this->requested_path . $this->params;
    
    $response = $this->request();
    
    return $response;
  }
  
  /**
   * {@inheritdoc}
   */
  public function get_ckan_datasets($sort) {
    
    $data_url = $this->base_url . $this->requested_path;
    $params = '?';
    $params .= '&rows=6'; 

    switch ($sort){
      case 'latest':
        $params .= '&sort=' . urlencode('metadata_modified desc');    
        break;
      case 'popular':
        $params .= '&sort=' . urlencode('extras_ga_view_count desc');    
        break;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data_url . $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);

    $responce = json_decode($result);
    if ($responce->success){
      $publications = $responce->result->results;
      return $publications;
    }
    
    return false;
  }
  
  public function get_ckan_dataset_count() {
    
    $res_formats = [
        'CSV','XML', 'XLS', 'XLSX', 'ODS', 'MDB', 'MDE', 'DBF',
        'SQL', 'SQLITE', 'DB', 'DBF', 'DBS', 'ODB', 'JSON', 
        'GEOJSON', 'KML', 'KMZ', 'SHP', 'SHX', 'WMS', 'WFS', 'WCS', 'CSW'];
    $attr = ['dataset', 'service'];
    $type = ['dataset', 'biodiversity_data'];

    $data_url = $this->base_url . $this->requested_path;

    $query = '?q=';
    $params = urlencode('res_format:('. implode(' OR ', $res_formats) .')');
    $params .= urlencode(' OR dcat_type:('. implode(' OR ', $attr) .')');
    $params .= urlencode(' OR type:('. implode(' OR ', $type) .')'); 
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data_url . $query. $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);

    $responce = json_decode($result, true);    
    if ($responce['success']){
        $count = $responce['result']['count'];
        return $count;
    }
    
    return false;
  }
  
  public function get_ckan_publications_count() {
    
    $res_formats = ['PDF','DOC', 'DOCX', 'ODF', 'ODT', 'EPUB', 'MOBI'];
    $attr = ['text'];
    $type = ['publications'];

    $data_url = $this->base_url . $this->requested_path;

    $query = '?q=';
    $params = urlencode('res_format:('. implode(' OR ', $res_formats) .')');
    $params .= urlencode(' OR dcat_type:('. implode(' OR ', $attr) .')');
    $params .= urlencode(' OR type:('. implode(' OR ', $type) .')');
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data_url . $query. $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);

    $responce = json_decode($result, true);    
    if ($responce['success']){
        $count = $responce['result']['count'];
        return $count;
    }
    
    return false;
  }
  
   public function get_ckan_organisations_count() {
     
    $data_url = $this->base_url . 'api/action/organization_list';

    $query = '';
    $params = '';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data_url . $query. $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);

    $responce = json_decode($result, true);    
      if ($responce['success']){
        $count = count($responce['result']);
        return $count;
    }   
    
    return false;
   }
   
   public function get_ckan_country_datasets_count($country_code){
    $data_url = $this->base_url . 'api/action/package_search';

    $query = '?q=';
    $params = 'member_countries:' . $country_code;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data_url . $query. $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);

    $responce = json_decode($result, true);    
      if ($responce['success']){
        $count = $responce['result']['count'];
        return $count;
    }   
    
    return false;
   }
  
}
