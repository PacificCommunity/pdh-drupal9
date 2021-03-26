<?php

/**
 * @file
 * Contains \Drupal\spc_hdb\Plugin\Block\spcHdbBanner.
 */
namespace Drupal\spc_hdb\Plugin\Block;

use Drupal\Core\Block\BlockBase;    
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;
use Drupal\Core\Link;
use Drupal\Core\Url;
/**
 * Provides a sps_hdb_banner.
 *
 * @Block(
 *   id = "spc_hdb_banner",
 *   admin_label = @Translation("SPC HDB Banner"),
 *   category = @Translation("SPC block")
 * )
 */
class spcHdbBanner extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $config = \Drupal::config('spc_hdb.settings');
    
    $title = $config->get('hdb_landing_title');
    $subtitle = $config->get('hdb_landing_subtitle');
      
    $data = [];
    $data['title'] = $title;
    $data['subtitle'] = $subtitle;
    $data['displaySubtitle'] = true;
    
    $current_path = \Drupal::service('path.current')->getPath();
    if (strpos($current_path, '/dashboard/health-dashboard/country/') !== false){
      $countries = $this->get_search_countries();
      foreach (\Drupal::routeMatch()->getParameters() as $param) {
        if (isset($countries, $countries[$param])){
          foreach ($countries as $key => $country){
              $term_data[] = [
               'id' => $key,
               'name' => $country['title'],
               'url' => '/dashboard/health-dashboard/country/' . $key
              ];
          }
          $data['terms'] = $term_data;
          $data['term'] = $countries[$param]['title'];          
        }
      }
    } else {
      $categories = $this->get_file_from_config('health_categories_fid');
      $categories = json_decode($categories, true);
      
      $indicators = $this->get_file_from_config('health_indicators_fid');
      $indicators = json_decode($indicators, true);
      
      $route_name = \Drupal::routeMatch()->getRouteName();
      foreach (\Drupal::routeMatch()->getParameters() as $param) {
        
        if (isset($categories, $categories[$param])) {
          $current_category = $param;
          foreach ($categories as $key => $category){
              $term_data[] = [
               'id' => $key,
               'name' => $category['name'],
               'url' => '/dashboard/health-dashboard/' . $key
              ];
          }
          $data['terms'] = $term_data;
          $data['term'] = $categories[$param]['name'];
        }
        
        if (isset($indicators, $indicators[$param])){
          foreach ($indicators as $key => $indicator){
            if ($indicator['indicator-category'] === $current_category){
              $subterm_data[] = [
               'id' => $key,
               'name' => $indicator['title'],
               'url' => '/dashboard/health-dashboard/' . $current_category . '/' . $key,
              ];
            }
          }
          $data['subterms'] = $subterm_data;
          $data['subterm'] = $indicators[$param]['title'];   
        }
      }
    }    

    $search_results = [
        'search_countries' => $this->get_search_countries(),
        'search_categories' => $this->get_search_categories(),
        'search_indicators' => $this->get_search_indicators(),
    ];

    return array(
      '#theme' => 'hdb_banner_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
      '#attached' => [
        'drupalSettings' => [
          'spc_hdb' => $search_results,
        ],
      ],        
    );
  }

  public function get_search_countries(){
    $countries = [];
      
    $data_values = $this->get_file_from_config('health_json_fid');
    $data_values = json_decode($data_values, true);
    $countries_data = $data_values['countries-data'];

    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('spc_hdb')->getPath(); 

    foreach($countries_data as $country_value){
      $countries[$country_value['id']] = [
        'code' => $country_value['id'], 
        'title' => $country_value['title'],
      ];
    }      

    return $countries;
  }
  
  public function get_search_categories(){
    $categories = [];
    
    $config = \Drupal::config('spc_hdb.settings');
    
    if ($fid = $config->get('health_categories_fid')){
      $file = File::load($fid);
      if (is_object($file)){
        $flag = file_create_url($file->getFileUri());
      }
   
      $file_path = file_create_url($file->getFileUri());
      $categories = json_decode(file_get_contents($file_path), true);
      
    }
 
    return $categories;
  }
  
  public function get_search_indicators(){
    $indicators = [];

    $config = \Drupal::config('spc_hdb.settings');
    
    if ($fid = $config->get('health_indicators_fid')){
      $file = File::load($fid);
      if (is_object($file)){
        $flag = file_create_url($file->getFileUri());
      }
   
      $file_path = file_create_url($file->getFileUri());
      $indicators = json_decode(file_get_contents($file_path), true);
      
    }

    return $indicators;
  }
  
  private function get_file_from_config($config_name){
    $config = \Drupal::config('spc_hdb.settings');
    $fid = $config->get($config_name);
    $file = File::load($fid);
    if (is_object($file)){
      $furl = file_create_url($file->getFileUri());
      $fcontent = file_get_contents($furl);
      return $fcontent;
    }
  }  
  
}
