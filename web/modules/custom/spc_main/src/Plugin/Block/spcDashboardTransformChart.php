<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcFooterMenu.
 */
namespace Drupal\spc_main\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

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
    $domains = $this->get_chart_data_path('domains');
    $goals = $this->get_chart_data_path('goals');

    return [
      '#theme' => 'spc_dashboard_transform_chart_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
      '#attached' => [
        'library' => ['spc/d3'],
        'drupalSettings' => [
            'spcMainCountriesData' => $countries,
            'spcMainDomainsDataPath' => $domains,
            'spcMainGoalsDataPath' => $goals,
            'spcMainModulePath' => drupal_get_path('module', 'spc_main'),
        ]  
       ],        
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  private function get_countries_data($file_name) {
    $data = [];
    $config = \Drupal::configFactory()->getEditable('spc_main.settings');
    $file_id = $config->get('sdg_' . $file_name . '_fid');
    
     if ($file_id) {
        $file_object = File::load($file_id);
        $file_uri = $file_object->getFileUri();
        $path = ltrim(file_url_transform_relative(file_create_url($file_uri)), '/');
        $spc_countries = file_get_contents($path);
        $data = json_decode($spc_countries, TRUE)[0];
     } else {
         $spc_countries = file_get_contents(drupal_get_path('module', 'spc_main') . '/data/' . $file_name . '.json');
         $data = json_decode($spc_countries, TRUE)[0];
     }

    return $data;
  }
  
  /**
   * {@inheritdoc}
   */
  private function get_chart_data_path($file_name) {
    $path = '';
    $config = \Drupal::configFactory()->getEditable('spc_main.settings');
    $file_id = $config->get('sdg_'.$file_name.'_fid');
    
    if ($file_id) {
        $file_object = File::load($file_id);
        $file_uri = $file_object->getFileUri();
        $path = str_replace('/system', '', file_url_transform_relative(file_create_url($file_uri)));
    } else {
        $path = '/' . drupal_get_path('module', 'spc_main') . '/data/' . $file_name . '.json';
    }

    return $path;
  }
}
