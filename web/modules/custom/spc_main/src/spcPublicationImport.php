<?php

namespace Drupal\spc_main;

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
  private function getParams(){
    
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
  public function getPublications() {
    
    $this->params = $this->getParams();
    $this->request_url = $this->base_url . $this->requested_path . $this->params;
    
    $response = $this->request();
    
    return $response;
  }
  
}
