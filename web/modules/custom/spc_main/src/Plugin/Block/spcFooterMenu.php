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
 *   id = "spc_footer_menu",
 *   admin_label = @Translation("SPC Footer Menu"),
 *   category = @Translation("SPC block")
 * )
 */
class spcFooterMenu extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $footer_markup = \Drupal::config('spc_data_import.settings')->get('footer_markup');
    $footer_markup_revision = \Drupal::config('spc_data_import.settings')->get('footer_markup_revision');
    $revision_apply = \Drupal::config('spc_data_import.settings')->get('revision_apply');    
    
    if (empty($footer_markup)){
      $spcDataImport = \Drupal::service('spc_pbank.spcDataImport');
      $response = $spcDataImport->getFooterMenu();
      
      if (is_object($response)){
        $main = $response->main[0];
        $footer_markup = $main;
      }
    } else {
      if ($revision_apply == 1 && !empty($footer_markup_revision)) {
        $footer_markup = $footer_markup_revision;
      }
    }
    
    return array(
      '#type' => 'markup',
      '#markup' => $footer_markup,
    );
  }
  
}
