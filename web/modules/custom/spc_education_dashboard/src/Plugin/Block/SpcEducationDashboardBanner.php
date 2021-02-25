<?php

namespace Drupal\spc_education_dashboard\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;

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
    $config = \Drupal::config('spc_education_dashboard.settings');

    $fid = $config->get('education_json_fid') ?? 0;
    $file = File::load($fid);

    if (is_object($file)){
      $json = json_decode(file_get_contents($file->getFileUri()), true);
      $data = $json['educationChartsData'] ?? [];
    }
    $details = [
      'tags' => array_column($data, 'id', 'name'),
    ];

    return [
      '#theme' => 'spc_education_dashboard_banner_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
      '#attached' => [
        'library' => [
          'spc_education_dashboard/search-autocomplete',
        ],
        'drupalSettings' => [
          'spc_education_dashboard_ac' => $details,
        ],
      ],
    ];

  }

}
