<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcFooterPartners.
 */
namespace Drupal\spc_main\Plugin\Block;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a custom_block.
 *
 * @Block(
 *   id = "spc_footer_partners",
 *   admin_label = @Translation("SPC Footer Partners"),
 *   category = @Translation("SPC block")
 * )
 */
class spcFooterPartners extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];
   
    $partners_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('partners');
    $theme = \Drupal::theme()->getActiveTheme();
    $theme_path = $theme->getPath();

    foreach($partners_tax as $partner_term){
      $partner = Term::load($partner_term->tid);
      $display_in = @$partner->get('field_display_in')->getValue();
      if (array_search('footer', array_column($display_in, 'value')) !== false){
        $name = $partner->getName();
        $url = @$partner->get('field_url')->getValue()[0]['value'];

        $fid = @$partner->get('field_footer_image')->getValue()[0]['target_id'];
        $file = File::load($fid);

        $icon = '';
        if (is_object($file)){
          $icon = file_create_url($file->getFileUri());
        }     

        $output[] = [
          'icon' => $icon,  
          'name' => $name,
          'url' => $url, 
        ];        
      }
    }
    
    return array(
      '#theme' => 'spc_footer_partners',
      '#partners' => $output,
    );
  }
  
}
