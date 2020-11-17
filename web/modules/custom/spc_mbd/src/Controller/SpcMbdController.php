<?php

namespace Drupal\spc_mbd\Controller;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Controller\ControllerBase;

class SpcMbdController extends ControllerBase {
    
    public function MbdLanding() {
        
        $config = \Drupal::config('spc_mbd.settings');
        
        $data['title'] =  $config->get('mbd_landing_title');
        $data['description'] = $config->get('mbd_landing_description');
        
        
        $data['maritime_zones'] = @$this->get_maritime_zones();
        if ($mbd_zones_fid = $config->get('mbd_zones_fid')){
            $mbd_zones_file = File::load($mbd_zones_fid);
            $data['maritime_zones_file'] =  file_create_url($mbd_zones_file->uri->value);           
        }

        $data['boundaries_treaty'] = @$this->get_boundaries_treaty();
        if ($mbd_boundary_treaty_fid = $config->get('mbd_boundary_treaty_fid')){
            $mbd_boundary_treaty_file = File::load($mbd_boundary_treaty_fid);
            $data['boundary_treaty_file'] =  file_create_url($mbd_boundary_treaty_file->uri->value);           
        }        
        
        $data['countries'] = @$this->get_countries();
        
        return [
            '#theme' => 'spc_mbd_landing',
            '#data' => $data,
        ];
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
              $flag = $file->url();
            } else {
                $flag = '/' . $theme_path . '/img/flags/' . $country_code . '.svg';
            }


            $aliasManager = \Drupal::service('path.alias_manager');
            $url = $aliasManager->getAliasByPath('/taxonomy/term/' . $country->id());

            $countries[] = [
              'url' => $url,
              'flag' => $flag,
              'name' => $name,
            ];
          }
        
        return $countries;
    }
    
    public function get_maritime_zones(){
        $zones = [];
        
        $zone_steps_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('maritime_zone');
        $theme = \Drupal::theme()->getActiveTheme();
        $theme_path = $theme->getPath();

        foreach($zone_steps_tax as $zone_step_term){
            $zone_step = Term::load($zone_step_term->tid);
            
            $name = $zone_step->getName();
            
            $fid = $zone_step->get('field_image')->getValue()[0]['target_id'];
            $file = File::load($fid);
                        
            $icon = '';
            if (is_object($file)){
              $icon = $file->url();
            }     
            
            $state = $this->get_combine_maritime_zones($name);

            $zones[] = [
              'icon' => $icon,  
              'name' => $name,
              'state' => $state, 
            ];
          }

        array_splice( $zones, 5, 0, '6' );
        $zones[5] = [
            'icon' => '/' . $theme_path . '/img/zone_steps/6.svg',  
            'name' => '6. Shared Boundary? Refer to <a href="#">Treaty Pathway</a>',
            'state' => 'na', 
        ];

        return $zones;
    }
    
    public function get_combine_maritime_zones($zone_name) {
        $combine_states = [];
        
        $countries_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('country');

        foreach($countries_tax as $country_term){
            $country = Term::load($country_term->tid);
            $maritime_zone_steps = $country->get('field_maritime_zone')->getValue();
            
            foreach ($maritime_zone_steps as $step){
                $paragraph_step = Paragraph::load($step['target_id']);
                $state = $paragraph_step->field_progress_type->value;

                $step_term_id = $paragraph_step->field_maritime_zone->getValue()[0]['target_id'];
                $paragraph_zone = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($step_term_id);
                $step_zone_name = $paragraph_zone->label();

                if ($zone_name == $step_zone_name){
                    $combine_states[$state]['count'] += 1;
                    $combine_states['total'] += 1;
                } else {
                    $combine_states[$state]['count'] += 0;
                    $combine_states[$state]['percent'] += 0;
                }
            }
        }
        
        foreach ($combine_states as $key => $value){
            if (!empty($value['count'])){
                $combine_states[$key]['percent'] = (100/$combine_states['total'])*$value['count'];                
            }
        }

        return $combine_states;
    }
    
    public function get_boundaries_treaty(){
        $treaty = [];
        
        $treaty_steps_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('boundary_treaty');
        $theme = \Drupal::theme()->getActiveTheme();
        $theme_path = $theme->getPath();
        
        foreach($treaty_steps_tax as $treaty_step_term){
            $treaty_step = Term::load($treaty_step_term->tid);
            
            $name = $treaty_step->getName();
            
            $fid = $treaty_step->get('field_image')->getValue()[0]['target_id'];
            $file = File::load($fid);
                        
            $icon = '';
            if (is_object($file)){
              $icon = $file->url();
            }     
            
            $state = $this->get_combine_boundaries_treaty($name);

            $treaty[] = [
              'icon' => $icon,  
              'name' => $name,
              'state' => $state, 
            ];
        }

        return $treaty;
    }
    
    public function get_combine_boundaries_treaty($treaty_name){
        $combine_states = [];

        $q = db_select('node','n')
            ->fields('n', ['nid'])
            ->condition('n.type', 'boundary_treaty');
        
        $results = $q->execute()->fetchAll();

        if (!empty($results)){
            foreach($results as $key => $value){
                $treaty = Node::load($value->nid);
                $steps = $treaty->get('field_boundaries_treaty_steps')->getValue();

                foreach($steps as $step){
                    if (!empty($step['target_id'])){
                        $paragraph_step = Paragraph::load($step['target_id']);
                        $state = $paragraph_step->field_progress_type->value;

                        $step_term_id = $paragraph_step->field_boundary_treaty->getValue()[0]['target_id'];
                        if ($step_term_id){
                            $paragraph_treaty = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($step_term_id);
                            $treaty_step_name = $paragraph_treaty->label();

                            if ($treaty_name == $treaty_step_name){
                                $combine_states[$state]['count'] +=1;
                                $combine_states['total'] +=1;
                            } else {
                                $combine_states[$state]['count'] +=0;
                                $combine_states['total'] +=0;
                            }
                        }
                    }
                }
            }
        }

        foreach ($combine_states as $key => $value){
            if (!empty($value['count'])){
                $combine_states[$key]['percent'] = (100/$combine_states['total'])*$value['count'];                
            }
        }        

        return $combine_states;
    }
    
    
}
