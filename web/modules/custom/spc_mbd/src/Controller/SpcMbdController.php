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
        
        $data['shelf_treaty'] = @$this->get_shelf_treaty();
        if ($mbd_shelf_treaty_fid = $config->get('mbd_shelf_treaty_fid')){
            $mbd_shelf_treat_file = File::load($mbd_shelf_treaty_fid);
            $data['shelf_treaty_file'] =  file_create_url($mbd_shelf_treat_file->uri->value);           
        }
        
        $data['partners'] = @$this->get_mbd_partners();
        $data['countries'] = @$this->get_countries();
        $map = @$this->get_map_data();

        return [
          '#theme' => 'spc_mbd_landing',
          '#data' => $data,
          '#attached' => [
              'library' => [
                'spc_mbd/terria',
              ],
              'drupalSettings' => [
                'spcMbd' => [
                    'map' => $map,
                ]
              ]
            ],
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
        $geojson = '[';
        
        $q = db_select('node','n')
            ->fields('n', ['nid'])
            ->condition('n.type', 'boundary_treaty');
        
        $results = $q->execute()->fetchAll();

        if (!empty($results)){
            foreach($results as $key => $value){
                $treaty = Node::load($value->nid);
                $steps = $treaty->get('field_boundaries_treaty_steps')->getValue();
                
                if ($line = $treaty->get('field_geojson_coordinates')->getValue()[0]['value']){
                  $geojson .= $line . ',';
                }

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
            
            $geojson =  substr($geojson, 0, -1) . ']';
            file_put_contents('modules/custom/spc_mbd/data/boundaries.json', $geojson);
        }

        foreach ($combine_states as $key => $value){
            if (!empty($value['count'])){
                $combine_states[$key]['percent'] = (100/$combine_states['total'])*$value['count'];                
            }
        }        

        return $combine_states;
    }
    
    public function get_shelf_treaty(){
        $treaty = [];
                
        $treaty_steps_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('continental_shelf');
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
            
            $state = '';
            $state = $this->get_combine_shelf_treaty_states($name);

            $treaty[] = [
              'icon' => $icon,  
              'name' => $name,
              'state' => $state, 
            ];
        }

        return $treaty;
    }
    
    public function get_combine_shelf_treaty_states($treaty_name){
        $combine_states = [];
        
        $q = db_select('node','n')
            ->fields('n', ['nid'])
            ->condition('n.type', 'continental_shelf');
        $results = $q->execute()->fetchAll();

        if (!empty($results)){
            foreach($results as $key => $value){
                $treaty = Node::load($value->nid);
                $steps = $treaty->get('field_shelf_steps')->getValue();
                
                foreach($steps as $step){
                    if (!empty($step['target_id'])){
                        $paragraph_step = Paragraph::load($step['target_id']);
                        $state = $paragraph_step->field_progress_type->value;

                        $step_term_id = $paragraph_step->field_shelf_step_term->getValue()[0]['target_id'];
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

    public function get_mbd_partners() {
        $output = [];
        
        $partners_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('partners');
        $theme = \Drupal::theme()->getActiveTheme();
        $theme_path = $theme->getPath();
        
        foreach($partners_tax as $partner_term){
            $partner = Term::load($partner_term->tid);
            
            $name = $partner->getName();
            
            $url = $partner->get('field_url')->getValue()[0]['value'];
            
            $fid = $partner->get('field_image')->getValue()[0]['target_id'];
            $file = File::load($fid);
                        
            $icon = '';
            if (is_object($file)){
              $icon = $file->url();
            }     

            $output[] = [
              'icon' => $icon,  
              'name' => $name,
              'url' => $url, 
            ];
        }

        return $output;
    }  
    
    public function get_map_data(){
      
        $map['limits'] = $this->get_limits_map_data();
        $map['eez'] = $this->get_eez_map_data();
        $map['shelf'] = $this->get_shelf_map_data();
        $map['boundary'] = $this->get_boundaries_map_data();
        //dump($map); die;
        return $map;
    }
    
    public function get_eez_map_data(){
        $data = [];
        $geojson = '[';
        
        $countries_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('country');
        $theme = \Drupal::theme()->getActiveTheme();
        $theme_path = $theme->getPath();

        foreach($countries_tax as $country_term){
            $term = Term::load($country_term->tid);
            
            $name = $term->getName();
            $country_code = $term->get('field_country_code')->getValue()[0]['value'];

            $fid = $term->get('field_flag')->getValue()[0]['target_id'];
            $file = File::load($fid);
            
            if (is_object($file)){
              $flag = $file->url();
            } else {
                $flag = '/' . $theme_path . '/img/flags/' . $country_code . '.svg';
            }

            $aliasManager = \Drupal::service('path.alias_manager');
            $url = $aliasManager->getAliasByPath('/taxonomy/term/' . $term->id());
            
            if ($plygon_json = $term->get('field_eez_plygon')->getValue()[0]['value']){
                $plygon_array = json_decode($plygon_json, true);

                $plygon_array['id'] = 'eez-' . $country_code;
                $geojson .= json_encode($plygon_array) . ',';
            }
            
            $country['country'] = [
              'url' => $url,
              'flag' => $flag,
              'name' => $name,
              'code' => $country_code,
            ];
  
            $country['area'] = $term->get('field_eez_area')->getValue()[0]['value'] ?? 'N/A';
            $country['deposited'] = $term->get('field_eez_deposited')->getValue()[0]['value'] ?? 'N/A';
            $country['date'] = $term->get('field_date_deposited')->getValue()[0]['value'] ?? 'N/A';
            $country['url'] = $term->get('field_url')->getValue()[0]['value'] ?? 'N/A';
            
            //ToDo - need clarification about calculetion.
            $country['treaties'] = '-';
            $country['pockets'] = '-';
            $country['shelf'] = '-';
            $country['ecs'] = '-';            

            $data['eez-' . $country_code] = $country;
          }
          
        $geojson =  substr($geojson, 0, -1) . ']';
        file_put_contents('modules/custom/spc_mbd/data/eez.json', $geojson);  
        
        return $data;
    }
    
    public function get_shelf_map_data(){
        $data = [];
        $geojson = '[';
        $data_base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
        
        $q = db_select('node','n')
            ->fields('n', ['nid'])
            ->condition('n.type', 'continental_shelf');
        $results = $q->execute()->fetchAll();

        if (!empty($results)){
            foreach($results as $key => $value){
                $limit = [];
                $node = Node::load($value->nid);
                $tid = $node->get('field_limit_countries')->getValue()[0]['target_id'];
                if ($term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid)){
                  $limit['country']['name'] = $term->label();
                  $limit['country']['code'] = $term->get('field_country_code')->value;
                  
                  $fid = $term->get('field_image')->getValue()[0]['target_id'];
                  $file = File::load($fid);
                  if (is_object($file)){
                    $limit['country']['flag'] = $file->url();
                  }
                }

                $limit['name'] = $node->getTitle() ?? '-';
                $limit['submission_done'] = $node->get('field_submission_done')->getValue()[0]['value'] ? 'Yes' : 'No';
                $limit['defence_year'] = $node->get('field_defence_year')->getValue()[0]['value'] ?? '-';
                $limit['established_year'] = $node->get('field_established_year')->getValue()[0]['value'] ?? '-';
                $limit['recommendation'] = strip_tags($node->get('field_recommendation')->getValue()[0]['value']) ?? '-';
                $limit['joint_submission'] = $node->get('field_joint_submission')->getValue()[0]['value'] ? 'Yes' : 'No';
                $limit['submission_complied'] = $node->get('field_full_submission_complied')->getValue()[0]['value'] ? 'Yes' : 'No';
                
                $limit['date'] = $node->get('field_date')->getValue()[0]['value'] ?? '-';
                
                $related_datasets = $node->get('field_related_datasets')->getValue();
                if($related_datasets){
                  foreach($related_datasets as $dataset_id){
                    $request = json_decode(file_get_contents($data_base_url . 'api/action/package_show?id=' . $dataset_id['value']), true);
                    if ($request['success']){
                      $dataset = [];
                      $dataset['title'] = $request['result']['title'];
                      $dataset['url'] = $data_base_url . $dataset_id;
                      $dataset['organization']['img'] = $data_base_url . 'uploads/group/' . $request['result']['organization']['image_url'];
                      $dataset['organization']['title'] = $request['result']['organization']['title'];
                      $dataset['organization']['url'] = $data_base_url . 'organization/'. $request['result']['organization']['name'];
                      $dataset['resources'] = $request['result']['resources'];
                      
                      $limit['datasets'][] = $dataset;
                    }
                  }
                }
                
                if ($line_json = $node->get('field_geojson_coordinates')->getValue()[0]['value']){
                  $line_array = json_decode($line_json, true);
                  $line_array['id'] = 'shelf-' . $value->nid;

                  $geojson .= json_encode($line_array) . ',';   
                  $data['shelf-' . $value->nid] = $limit;
                }
            }

            $geojson =  substr($geojson, 0, -1) . ']';
            file_put_contents('modules/custom/spc_mbd/data/shelf.json', $geojson);            
        }  
        
        return $data;
    }    

    public function get_limits_map_data() {
        $data = [];
        $geojson = '[';
        $data_base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
        
        $q = db_select('node','n')
            ->fields('n', ['nid'])
            ->condition('n.type', 'high_seas_limit');
        $results = $q->execute()->fetchAll();

        if (!empty($results)){
            foreach($results as $key => $value){
                $limit = [];
                $node = Node::load($value->nid);
                $tid = $node->get('field_limit_countries')->getValue()[0]['target_id'];
                if ($term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid)){
                  $limit['country']['name'] = $term->label();
                  $limit['country']['code'] = $term->get('field_country_code')->value;
                  
                  $fid = $term->get('field_image')->getValue()[0]['target_id'];
                  $file = File::load($fid);
                  if (is_object($file)){
                    $limit['country']['flag'] = $file->url();
                  }
                }

                $limit['deposited'] = $node->get('field_deposited')->getValue()[0]['value'] ? 'Yes' : 'No';
                $limit['date'] = $node->get('field_date_deposited')->getValue()[0]['value'];
                $limit['url'] = $node->get('field_url')->getValue()[0]['value'];
                
                $related_datasets = $node->get('field_related_datasets')->getValue();
                if($related_datasets){
                  foreach($related_datasets as $dataset_id){
                    $request = json_decode(file_get_contents($data_base_url . 'api/action/package_show?id=' . $dataset_id['value']), true);
                    if ($request['success']){
                      $dataset = [];
                      $dataset['title'] = $request['result']['title'];
                      $dataset['url'] = $data_base_url . $dataset_id;
                      $dataset['organization']['img'] = $data_base_url . 'uploads/group/' . $request['result']['organization']['image_url'];
                      $dataset['organization']['title'] = $request['result']['organization']['title'];
                      $dataset['organization']['url'] = $data_base_url . 'organization/'. $request['result']['organization']['name'];
                      $dataset['resources'] = $request['result']['resources'];
                      
                      $limit['datasets'][] = $dataset;
                    }
                  }
                }
                
                if ($line_json = $node->get('field_geojson_coordinates')->getValue()[0]['value']){
                  $line_array = json_decode($line_json, true);
                  $line_array['id'] = 'limit-' . $value->nid;

                  $geojson .= json_encode($line_array) . ',';
                  $data['limit-' . $value->nid] = $limit;
                }
            }

            $geojson =  substr($geojson, 0, -1) . ']';
            file_put_contents('modules/custom/spc_mbd/data/limits.json', $geojson);            
        } 

      return $data;
    }
    
    public function get_boundaries_map_data() {
        $data = [];
        $geojson = '[';
        $data_base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
        
        $q = db_select('node','n')
            ->fields('n', ['nid'])
            ->condition('n.type', 'boundary_treaty');
        $results = $q->execute()->fetchAll();

        if (!empty($results)){
            foreach($results as $key => $value){
                $limit = [];
                $node = Node::load($value->nid);
                
                $tid_one = $node->get('field_boundary_country_one')->getValue()[0]['target_id'];
                if ($term_one = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid_one)){
                  $limit['country_one']['name'] = $term_one->label();
                  $limit['country_one']['code'] = $term_one->get('field_country_code')->value;
                  
                  $fid_one = $term_one->get('field_image')->getValue()[0]['target_id'];
                  $file_one = File::load($fid_one);
                  if (is_object($file_one)){
                    $limit['country_one']['flag'] = $file_one->url();
                  }
                }
                
                $tid_two = $node->get('field_boundary_country_two')->getValue()[0]['target_id'];
                if ($term_two = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid_two)){
                  $limit['country_two']['name'] = $term_two->label();
                  $limit['country_two']['code'] = $term_two->get('field_country_code')->value;
                  
                  $fid_two = $term_two->get('field_image')->getValue()[0]['target_id'];
                  $file_two = File::load($fid_one);
                  if (is_object($file_two)){
                    $limit['country_two']['flag'] = $file_two->url();
                  }
                }                

                $limit['signed'] = $node->get('field_signed')->getValue()[0]['value']  ?? '-';
                $limit['year_signed'] = $node->get('field_year_only')->getValue()[0]['value']  ?? '-';
                $limit['force'] = $node->get('field_into_force')->getValue()[0]['value']  ?? '-';
                $limit['date'] = $node->get('field_date')->getValue()[0]['value'] ?? '-';
                $limit['url'] = $node->get('field_url')->getValue()[0]['value'] ?? '-';
                
                $related_datasets = $node->get('field_related_datasets')->getValue();
                if($related_datasets){
                  foreach($related_datasets as $dataset_id){
                    $request = json_decode(file_get_contents($data_base_url . 'api/action/package_show?id=' . $dataset_id['value']), true);
                    if ($request['success']){
                      $dataset = [];
                      $dataset['title'] = $request['result']['title'];
                      $dataset['url'] = $data_base_url . $dataset_id;
                      $dataset['organization']['img'] = $data_base_url . 'uploads/group/' . $request['result']['organization']['image_url'];
                      $dataset['organization']['title'] = $request['result']['organization']['title'];
                      $dataset['organization']['url'] = $data_base_url . 'organization/'. $request['result']['organization']['name'];
                      $dataset['resources'] = $request['result']['resources'];
                      
                      $limit['datasets'][] = $dataset;
                    }
                  }
                }
                
                if ($line_json = $node->get('field_geojson_coordinates')->getValue()[0]['value']){
                  $line_array = json_decode($line_json, true);
                  $line_array['id'] = 'boundary-' . $value->nid;

                  $geojson .= json_encode($line_array) . ',';
                  $data['boundary-' . $value->nid] = $limit;
                }
            }

            $geojson =  substr($geojson, 0, -1) . ']';
            file_put_contents('modules/custom/spc_mbd/data/boundaries.json', $geojson);            
        } 

      return $data;
    }    

}
