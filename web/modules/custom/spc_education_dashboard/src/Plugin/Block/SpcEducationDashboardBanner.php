<?php

namespace Drupal\spc_education_dashboard\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "spc_education_dashboard_banner",
 *   admin_label = @Translation("SPC Education Dashboard Banner"),
 *   category = @Translation("SPC Education Dashboard")
 * )
 */
class SpcEducationDashboardBanner extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [];
    $search_results = [];
    return [
      '#theme' => 'spc_education_dashboard_banner_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
      '#attached' => [
        'drupalSettings' => [
          'spc_education_dashboard' => $search_results,
        ],
      ],
    ];

  }

}
