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
    
    return array(
      '#theme' => 'spc_dashboard_transform_chart_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
      '#attached' => array(
        'library' => array('spc/d3'),
       ),        
    );
  }
  
}
