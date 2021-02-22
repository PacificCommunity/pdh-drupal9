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
    
    $route_name = \Drupal::routeMatch()->getRouteName();
    
    $entities = [];
    foreach (\Drupal::routeMatch()->getParameters() as $param) {
      if ($param instanceof \Drupal\Core\Entity\EntityInterface) {
        $entities[] = $param;
      }
    }    
    
    if (!empty($entities) && is_object($entities[0])){
        if ($entities[0]->getEntityTypeId() == 'taxonomy_term'){
          $tax_entity = $entities[0];
          $data['term'] = $tax_entity->getName();
          $data['vocabulary'] = $tax_entity->bundle();
          
          //Breadcrumbs.
          $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($data['vocabulary']);
          foreach ($terms as $term) {
            $aliasManager = \Drupal::service('path_alias.manager');
            $url = $alias = $aliasManager->getAliasByPath('/taxonomy/term/' . $term->tid);
            
            $term_data[] = array(
             'id' => $term->tid,
             'name' => $term->name,
             'url' => $url
            );
          }
          $data['terms'] = $term_data;
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
  
}
