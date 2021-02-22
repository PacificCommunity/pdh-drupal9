<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcBanner.
 */
namespace Drupal\spc_main\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a spc_banner.
 *
 * @Block(
 *   id = "spc_banner",
 *   admin_label = @Translation("SPC Banner"),
 *   category = @Translation("SPC block")
 * )
 */
class spcBanner extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->getConfiguration();
    $data = [];
    $data['title'] = isset($config['spc_banner_title']) ? $config['spc_banner_title'] : '';
    
    $aliasManager = \Drupal::service('path_alias.manager');
    $current_path = \Drupal::service('path.current')->getPath();
    $url = $aliasManager->getAliasByPath($current_path);
    $spc_banner_title = strip_tags($config['spc_banner_title']);
    
    if ($spc_banner_title == 'All Stories'){
      $spc_banner_title = 'Story';
    } 
    
    $data['breads'][] = [
      'name' => isset($config['spc_banner_title']) ? $spc_banner_title : substr($url, 0, strripos($url, '/')),
      'url' => substr($url, 0, strripos($url, '/')), 
    ];

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface && $node->getType() == 'data_insights' || $node instanceof \Drupal\node\NodeInterface && $node->getType() == 'article') {
      $data['breads'][] = ['name' => $node->getTitle()];
    }
    
    if ($node->getType() === 'page') {
      $title = $node->getTitle();
      $title_parts = explode(' ', $title);
      $title_parts[0] = '<strong>' . $title_parts[0] . '</strong>';
      $title = implode(' ', $title_parts);

      $data['base_page_title'] = $title;

      if ($node->hasField('field_banner_image')) {
        $banner_image_entity = $node->field_banner_image->entity;

        if ($banner_image_entity !== null) {
          $banner_image_url = file_create_url($banner_image_entity->getFileUri());
          $data['banner_image_url'] = $banner_image_url;
        }
      }

      if ($node->hasField('field_header_type')) {
        $header_type = $node->get('field_header_type')->getValue()[0]['value'];
        if ($header_type) {
          $data['maximum_header'] = boolval($header_type);
        }
      }
    }

    return array(
      '#theme' => 'main_banner_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['spc_banner_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter the banner title'),
      '#description' => 'Example: <strong>Data</strong> Insights',
      '#default_value' => isset($config['spc_banner_title']) ? $config['spc_banner_title'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['spc_banner_title'] = $values['spc_banner_title'];
  }

}
