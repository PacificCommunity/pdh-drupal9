<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcFooterMenu.
 */
namespace Drupal\spc_main\Plugin\Block;


use Drupal\Core\Block\BlockBase;
/**
 * Provides a custom_block.
 *
 * @Block(
 *   id = "spc_dashboard_transform_chart_block",
 *   admin_label = @Translation("SPC dashboard transform chart"),
 *   category = @Translation("SPC block")
 * )
 */
class spcDashboardTransformChart extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    # Add library
    $variables['#attached']['library'][] =  'library_ex/library_ex';   
    
    $data['title'] = 'SDGs Progress Wheels';
    $countries = $this->get_countries_data('countries');

    return [
      '#theme' => 'spc_dashboard_transform_chart_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
      '#attached' => [
        'library' => ['spc/d3'],
        'drupalSettings' => [
            'spcMainCountriesData' => $countries,
            'spcMainModulePath' => drupal_get_path('module', 'spc_main'),
        ]  
       ],        
    ];
  }

  private function get_countries_data($default_file) {

    $data = [];

    if ($spc_countries = file_get_contents(drupal_get_path('module', 'spc_main') . '/data/' . $default_file . '.json')) {
      $data = json_decode($spc_countries, TRUE)[0];
    }

    return $data;
  }
  
}
