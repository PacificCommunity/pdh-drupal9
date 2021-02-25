<?php

namespace Drupal\spc_hdb\Controller;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Controller\ControllerBase;

class SpcHdbController extends ControllerBase {
  
  
    public $limit = 682;
    
    public function hdbLanding() {
      
        $config = \Drupal::config('spc_hdb.settings');

        $background = $config->get('hdb_landing_description');
        if (strlen($background) > $this->limit) {
          $data['description']['less'] = substr($background, 0, $this->limit);
          $data['description']['more'] = substr($background, $this->limit, strlen($background));
        } else {
          $data['description']['less'] = $background;
        }
        
        $summary_chart = @$this->get_summary_chart();
        $summary_chart_fid = $config->get('health_chart_download_fid');
        $file = File::load($summary_chart_fid);
        if (is_object($file)){
          $data['summary_chart_download'] = file_create_url($file->getFileUri());
        }
        
        $data['categories'] = @$this->get_hdb_categories();

        $data['countries'] = @$this->get_countries();
        
        return [
          '#theme' => 'spc_hdb_landing',
          '#data' => $data,
          '#attached' => [
            'library' => [
              'spc_hdb/hdb',
              'spc/d3v3',  
            ],
            'drupalSettings' => [
              'spc_hdb' => [
                'summary_chart' => $summary_chart,
              ],
            ],
          ],
        ];
    }
    
    public function hdbCategory($category){
      $categories = $this->get_file_from_config('health_categories_fid');
      $categories = json_decode($categories, true);
      
      if (!isset($categories, $categories[$category])) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }

      $data['category'] = $categories[$category];
      
      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('spc_hdb')->getPath();
      $data['module']['path'] = $module_path;

      $description = $categories[$category]['description'];
      $data['category']['id'] = $category;
      $data['category']['description'] = [];
      if (strlen($description) > $this->limit) {
        $data['category']['description']['less'] = substr($description, 0, $this->limit);
        $data['category']['description']['more'] = substr($description, $this->limit, strlen($description));
      } else {
        $data['category']['description']['less'] = $description;
      }
      
      $data['categories'] = $categories;

      $indicators = $this->get_file_from_config('health_indicators_fid');
      $indicators = json_decode($indicators, true);
      $data['indicators'] = $indicators;
      
      $data_values = $this->get_file_from_config('health_json_fid');
      $data_values = json_decode($data_values, true);
      $data['values'] = $data_values['countries-data'];

      if (count($data['category']['indicators']) <= 3){
        $data['category_countries_class'] = 'col-sm-6';
        $data['category_detales_class'] = 'col-sm-6';
      } else {
        $data['category_countries_class'] = 'col-sm-12';
        $data['category_detales_class'] = 'col-sm-12';
      }

      return [
        '#theme' => 'spc_hdb_category',
        '#data' => $data,
        '#attached' => [
          'library' => [
            'spc_hdb/hdb',
            'spc/d3v3',  
          ],
          'drupalSettings' => [
            'spc_hdb' => [
              'indicator_detales' => $indicators,
            ],
          ],
        ],          
      ];
    }
    
    public function hdbIndicator($category, $indicator){
      
      $indicators = $this->get_file_from_config('health_indicators_fid');
      $indicators = json_decode($indicators, true);

      if (!isset($indicators, $indicators[$indicator])) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
      
      $data['indicator'] = $indicators[$indicator];      
      $data['indicators'] = $indicators;
      
      $data_values = $this->get_file_from_config('health_json_fid');
      $data_values = json_decode($data_values, true);
      $data['countries'] = $data_values['countries-data'];

      $categories = $this->get_file_from_config('health_categories_fid');
      $categories = json_decode($categories, true);
      $data['category_data'] = $categories[$category];

      $description = $indicators[$indicator]['indicator-description'];
      if (strlen($description) > $this->limit) {
        $data['indicator']['description']['less'] = substr($description, 0, $this->limit);
        $data['indicator']['description']['more'] = substr($description, $this->limit, strlen($description));
      } else {
        $data['indicator']['description']['less'] = $description;
      }

      $data['current_indicator'] = $indicator;
      $data['current_category'] = $category; 

      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('spc_hdb')->getPath();
      $data['module']['path'] = $module_path;
      
      //dump($data);
      $map = [];      

      return [
        '#theme' => 'spc_hdb_indicator',
        '#data' => $data,
        '#attached' => [
          'library' => [
            'spc_hdb/hdb',
            'spc/d3v3',  
          ],
          'drupalSettings' => [
            'spc_hdb' => [
              'country_chart' => $map,
            ],
          ],
        ],          
      ];
    }
    
    public function hdbCountry($country){
      $health_dashboard_data = $this->get_file_from_config('health_json_fid');
      $health_dashboard_data = json_decode($health_dashboard_data, true);
      
      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('spc_hdb')->getPath();      
      $categories = json_decode(file_get_contents($module_path . '/data/categories.json'), true);
      
      $indicators = $this->get_file_from_config('health_indicators_fid');
      $indicators = json_decode($indicators, true);
      
      $countries = @$this->get_countries();
      if (!isset($countries, $countries[$country])) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
      
      foreach($health_dashboard_data['countries-data'] as $country_data){
        if ($country_data['id'] == $country){
          
          $data['title'] = $country_data['title'];
          $data['country'] = $country_data['title'];
          $data['country_id'] = $country_data['id'];
          $data['indicators'] = $country_data['indicators'];
          $data['categories'] = $categories;
          
          $data['flag']['src'] = '/' . $module_path . '/img/flags/' . $data['country_id'] . '.svg';
          $data['map']['url'] = '/' . $module_path . '/img/maps/' . $data['country_id'] . '.svg';
          
          $summary_chart = $this->get_country_summary_chart_data($country_data['indicators']);

        }
      }

      return [
        '#theme' => 'spc_hdb_country',
        '#data' => $data,
        '#attached' => [
          'library' => [
            'spc_hdb/hdb',
            'spc/d3v3',  
          ],
          'drupalSettings' => [
            'spc_hdb' => [
              'summary_chart' => $summary_chart,
              'indicator_detales' => $indicators  
            ],
          ],
        ],          
      ];
    }
    
    public function get_summary_chart(){

      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('spc_hdb')->getPath();      
      $summary_chart_data = json_decode(file_get_contents($module_path . '/data/summary_chart.json'), true);
      
      return $summary_chart_data;
    }
    
    public function get_hdb_categories(){

      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('spc_hdb')->getPath();      
      $categories = json_decode(file_get_contents($module_path . '/data/categories.json'), true);
      
      $category_menu = [];
      foreach ($categories as $key => $value) {
        if ($value['wrapper']) {
          $categories[$key]['wrapper'] = FALSE;
          $indicators[$key] = $value;
          $category_menu['wrapper'] = [
            'title' => $value['wrapper'],
            'indicators' => $indicators,
          ];
        }
        else {
          $category_menu[$key] = $categories[$key];
        }
      }

      return $category_menu;
    }    
    
    public function get_countries(){
        $countries = [];
        
        $countries_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('country');
        $theme = \Drupal::theme()->getActiveTheme();
        $theme_path = $theme->getPath();

        foreach($countries_tax as $country_term){
            $country = Term::load($country_term->tid);
            
            $name = $country->getName();
            $country_code = $country->get('field_country_code')->getValue()[0]['value'];
            $country_id = str_replace(' ', '-', strtolower($name));
            
            $fid = @$country->get('field_flag')->getValue()[0]['target_id'];
            $file = File::load($fid);
            
            if (is_object($file)){
              $flag = file_create_url($file->getFileUri());
            } else {
                $flag = '/' . $theme_path . '/img/flags/' . $country_code . '.svg';
            }

            $url = '/dashboard/health-dashboard/country/' . $country_id;

            $countries[$country_id] = [
              'id' => $country_id, 
              'code' => $country_code,
              'url' => $url,
              'flag' => $flag,
              'name' => $name,
            ];
          }
        
        return $countries;
    }
    
    function get_country_summary_chart_data($country_data){
      $summary = array();

      foreach($country_data as $key => $indicator){
        switch ($indicator['value']){
          case 'present':
          case 'low':
          case 'medium':
          case 'high':  
              @$summary[$indicator['indicator-category']]['present'] += 1;
            break;  
          case 'under-development':
              @$summary[$indicator['indicator-category']]['under-development'] += 1;
            break;
          case 'not-present':
              @$summary[$indicator['indicator-category']]['not-present'] += 1;
            break;
        }
      } 

      $summary_filtred = [];
      foreach($summary as $key => $detales){
        $count = 0;
        $count = @$detales['present'] + @$detales['under-development'] + @$detales['not-present'];

        $summary_filtred[] = [
          'indicator' => ucfirst(str_replace('-', ' ', $key)),
          'present' => (@$detales['present']/$count) * 100,
          'under-development' => (@$detales['under-development']/$count) * 100,
          'not-present' => (@$detales['not-present']/$count) * 100
          ] ;
      }

      return $summary_filtred;
    }
    
    private function get_file_from_config($config_name){
      $config = \Drupal::config('spc_hdb.settings');
      $fid = $config->get($config_name);
      $file = File::load($fid);
      if (is_object($file)){
        $furl = file_create_url($file->getFileUri());
        $fcontent = file_get_contents($furl);
        return $fcontent;
      }
    }
    
}
