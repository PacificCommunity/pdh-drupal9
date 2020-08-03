<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcMainMenu.
 */
namespace Drupal\spc_main\Plugin\Block;


use Drupal\Core\Block\BlockBase;
/**
 * Provides a custom_block.
 *
 * @Block(
 *   id = "spc_main_menu",
 *   admin_label = @Translation("SPC Main Menu"),
 *   category = @Translation("SPC block")
 * )
 */
class spcMainMenu extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $header_markup = \Drupal::config('spc_data_import.settings')->get('header_markup');
    $header_markup_revision = \Drupal::config('spc_data_import.settings')->get('header_markup_revision');
    $revision_apply = \Drupal::config('spc_data_import.settings')->get('revision_apply');
    
    if (empty($header_markup)){
      $spcDataImport = \Drupal::service('spc_main.spcDataImport');
      $response = $spcDataImport->getHeaderMenu();

      if (is_object($response)){
        $main = $response->main[0];
        $mobile = $response->mobile[0];
        $header_markup = $main . $mobile;
      }      
    } else {
      if ($revision_apply == 1 && !empty($header_markup_revision)) {
        $header_markup = $header_markup_revision;
      }
    }
    
    return array(
      '#theme' => 'spc_main_menu',
      '#menu' => $header_markup,
    );
  }
  
}
