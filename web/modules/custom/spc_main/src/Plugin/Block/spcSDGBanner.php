<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcBanner.
 */
namespace Drupal\spc_main\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a spc_sdg_banner.
 *
 * @Block(
 *   id = "spc_sdg_banner",
 *   admin_label = @Translation("SPC SDG Banner"),
 *   category = @Translation("SPC block")
 * )
 */
class spcSDGBanner extends BlockBase {
  
  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->getConfiguration();
    $data = [];
    $title = '';
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $title = $node->get('field_dsp_title_markup')->getString();
    }
    $data['title'] = (isset($config['spc_sdg_banner_title']) && !empty($config['spc_sdg_banner_title'])) ? $config['spc_sdg_banner_title'] : $title;
    $nids = \Drupal::entityQuery('node')->condition('type','dsp')->execute();
    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $node->id());
      $data['dsps'][] = [
        'url' => $url_alias, //$node->toUrl()->toString(),
        'title' => $node->getTitle()
      ];
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

    $form['spc_sdg_banner_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter the banner title'),
      '#description' => 'Example: <strong>Data</strong> Insights',
      '#default_value' => isset($config['spc_sdg_banner_title']) ? $config['spc_sdg_banner_title'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['spc_sdg_banner_title'] = $values['spc_sdg_banner_title'];
  }

}
