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
    
    $data['breads'][] = [
      'name' => isset($config['spc_banner_title']) ? strip_tags($config['spc_banner_title']) : substr($url, 0, strripos($url, '/')),
      'url' => substr($url, 0, strripos($url, '/')), 
    ];

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface && $node->getType() == 'data_insights' 
        || $node instanceof \Drupal\node\NodeInterface && $node->getType() == 'article') {
      $data['breads'][] = ['name' => $node->getTitle()];
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
