<?php

namespace Drupal\spc_education_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;


/**
 * Returns responses for SPC Education Dashboard routes.
 */
class SpcEducationDashboardController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function index() {
    $data = ['items' => []];
    $config = \Drupal::config('spc_education_dashboard.settings');

    $fid = $config->get('education_json_fid') ?? 0;
    $file = File::load($fid);

    if (is_object($file)){
      $json = json_decode(file_get_contents($file->getFileUri()), true);
      $data["items"] = $json['educationChartsData'] ?? [];
    }
    $js_data = [];

    foreach ($data['items'] as $chart_data) {
      $charts =  $chart_data['charts'];

      //hiding chart countries name.
      foreach ($charts as $chart_id => $chart) {
        foreach (array_keys($chart['data']) as $key) {
          $charts[$chart_id]['data'][$key]['country'] = $key;
        }
      }
      $js_data['chart' . $chart_data['id']] = $charts;
      $threshold = $chart_data['threshold'];
      $js_data['threshold' . $chart_data['id']] = $threshold;
    }

    return [
      '#theme' => 'spc_education_dashboard_landing',
      '#data' => $data,
      '#attached' => [
        'library' => [
          'spc_education_dashboard/charts',
          'spc/d3v3',
        ],
        'drupalSettings' => [
          'spc_education_dashboard' => $js_data,
        ],
      ],
    ];

  }

}
