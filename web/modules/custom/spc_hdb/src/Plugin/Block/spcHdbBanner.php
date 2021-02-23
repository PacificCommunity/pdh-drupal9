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
    
    $categories = $this->get_file_from_config('health_categories_fid');
    $categories = json_decode($categories, true);
    $route_name = \Drupal::routeMatch()->getRouteName();
    foreach (\Drupal::routeMatch()->getParameters() as $param) {
      if (isset($categories, $categories[$param])) {
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

    $countries_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('country');
    foreach($countries_tax as $country_term){
        $country = Term::load($country_term->tid);

        $name = $country->getName();
        $country_code = $country->get('field_country_code')->getValue()[0]['value'];
        
        $key = str_replace(' ', '-', strtolower($name));

        $countries[$key] = [
          'title' => $name,
          'code'  => $country_code
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
