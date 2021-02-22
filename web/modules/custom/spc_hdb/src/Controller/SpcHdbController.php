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
    
    public function hdbLanding() {
      
        $config = \Drupal::config('spc_hdb.settings');

        $background = $config->get('hdb_landing_description');
        $limit = 682;
        
        if (strlen($background) > $limit) {
          $data['description']['less'] = substr($background, 0, $limit);
          $data['description']['more'] = substr($background, $limit, strlen($background));
        } else {
          $data['description']['less'] = $background;
        }
        
        $summary_chart = @$this->get_summary_chart();
        $summary_chart_fid = $config->get('health_chart_download_fid');
        $file = File::load($summary_chart_fid);
        $data['summary_chart_download'] = file_create_url($file->getFileUri());
        
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
    
    public function hdbCountry(){
      
        $data['country'] = $code;
        
        return [
            '#markup' => '<h1>as'.$code.'</h1>',
            '#data' => $data,
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

            $fid = @$country->get('field_flag')->getValue()[0]['target_id'];
            $file = File::load($fid);
            
            if (is_object($file)){
              $flag = file_create_url($file->getFileUri());
            } else {
                $flag = '/' . $theme_path . '/img/flags/' . $country_code . '.svg';
            }


            $aliasManager = \Drupal::service('path_alias.manager');
            $url = $aliasManager->getAliasByPath('/taxonomy/term/' . $country->id());

            $countries[] = [
              'url' => $url,
              'flag' => $flag,
              'name' => $name,
            ];
          }
        
        return $countries;
    }
    
}
