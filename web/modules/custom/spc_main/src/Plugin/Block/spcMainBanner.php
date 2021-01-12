<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcMainBanner.
 */
namespace Drupal\spc_main\Plugin\Block;

use Drupal\Core\Block\BlockBase;    
use Drupal\Core\Link;
use Drupal\Core\Url;
/**
 * Provides a sps_main_banner.
 *
 * @Block(
 *   id = "sps_main_banner",
 *   admin_label = @Translation("SPC Main Banner"),
 *   category = @Translation("SPC block")
 * )
 */
class spcMainBanner extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $title = 'Pacific <strong>Data</strong> Hub';
    $subtitle = 'Harnessing the power of data and knowledge for sustainable development';
      
    $data = [];
    $data['title'] = $title;
    $data['subtitle'] = $subtitle;
    
    $route_name = \Drupal::routeMatch()->getRouteName();
    
    $entities = [];
    foreach (\Drupal::routeMatch()->getParameters() as $param) {
      if ($param instanceof \Drupal\Core\Entity\EntityInterface) {
        $entities[] = $param;
      }
    }    
    
    $data['search_form'] = \Drupal::formBuilder()->getForm('Drupal\spc_main\Form\AdvancedSearchForm');
    
    $spc_home_banner['dataBaseUrl'] = DATA_BASE_URL;
    
    $module_path = drupal_get_path('module', 'spc_main');
    $mapping_config = json_decode(file_get_contents($module_path . '/data/mapping.json'), true);    
    $spc_home_banner['mappingConfig'] = $mapping_config;
    
    return array(
      '#theme' => 'main_banner_block',
      '#attached' => [
          'drupalSettings' => [
              'spc_home_banner' => $spc_home_banner
            ]
          ],
      '#cache' => [
          'max-age'=> 0
          ],
      '#data' => $data,
    );
  }
}
