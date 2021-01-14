<?php

namespace Drupal\spc_data_import;

/**
 * spcDataImport Description.
 *
 */
class spcDataImport {
  
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
  public function __construct(){
    
    $this->base_url = \Drupal::config('spc_data_import.settings')->get('spc_base_url');
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
      watchdog_exception('spc_main', $e->getMessage());
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function getHeaderMenu() {
      
    $this->requested_path = 'menu_export';
    $this->request_url = $this->base_url . $this->requested_path;
    
    $response = $this->request();
    
    return $response;
    
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFooterMenu() {
    
    $this->requested_path = 'footer_export';
    $this->request_url = $this->base_url . $this->requested_path;
    
    $response = $this->request();
    
    return $response;
  }

}
