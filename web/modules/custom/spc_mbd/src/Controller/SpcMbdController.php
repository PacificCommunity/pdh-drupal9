<?php

namespace Drupal\spc_mbd\Controller;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

class SpcMbdController extends ControllerBase {
    
    public function MbdLanding() {
        
        $config = \Drupal::config('spc_mbd.settings');
        
        $data['title'] =  $config->get('mbd_landing_title');
        $data['description'] = $config->get('mbd_landing_description');
        
        $data['maritime_zones'] = @$this->get_maritime_zones();
        //dump($data['maritime_zones']); die;
        
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

            $state = '';

            $zones[] = [
              'icon' => $icon,  
              'name' => $name,
              'state' => $state, 
            ];
          }        
        
        return $zones;
    }
    
}
