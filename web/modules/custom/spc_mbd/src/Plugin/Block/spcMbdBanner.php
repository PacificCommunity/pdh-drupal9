<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcMbdBanner.
 */
namespace Drupal\spc_mbd\Plugin\Block;

use Drupal\Core\Block\BlockBase;   
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Link;
use Drupal\Core\Url;
/**
 * Provides a sps_mbd_banner.
 *
 * @Block(
 *   id = "sps_mbd_banner",
 *   admin_label = @Translation("SPC MBD Banner"),
 *   category = @Translation("SPC block")
 * )
 */
class spcMbdBanner extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $title = 'Pacific <strong>Maritime Boundaries</strong> Dashboard';
    $subtitle = '';
      
    $data = [];
    $data['title'] = $title;
    $data['subtitle'] = $subtitle;
    $data['displaySearch'] = false;
    $data['displaySubtitle'] = false;
    
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
            $Taxonomy = Term::load($term->tid);
            $published_status = $Taxonomy->get('status')->getValue()[0]['value'];
            if ($published_status){
              $aliasManager = \Drupal::service('path_alias.manager');
              $url = $alias = $aliasManager->getAliasByPath('/taxonomy/term/' . $term->tid);

              $term_data[] = array(
               'id' => $term->tid,
               'name' => $term->name,
               'url' => $url
              );
            }
          }
          $data['terms'] = $term_data;
        }
    }
    
    return array(
      '#theme' => 'mbd_banner_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
    );
  }
}
