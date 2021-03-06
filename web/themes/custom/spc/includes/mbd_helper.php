<?php

use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\image\Entity\ImageStyle;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\spc_mbd\Controller\SpcMbdController;

function _get_related_stories($node){
  $field_related_data_insights = $node->get('field_related_stories')->getValue();
  $spc_base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
  $stories = [];

  foreach ($field_related_data_insights as $target){
    $nid = $target['target_id'];
    $story = Node::load($nid);
    
    if (is_object($story)){
      $data['title'] = $story->getTitle();
      $data['url'] = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid);

      $style = ImageStyle::load('stories_slides');
      $styled_image_url = $style->buildUrl($story->field_image->entity->getFileUri());
      $data['img'] = $styled_image_url;
              
      $stories[] = $data;
    }
  }
  
  return $stories;
}

function _get_related_datasets($node){
  $field_related_datasets = $node->get('field_related_datasets')->getValue();
  $spc_base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
  $detales = [];

  foreach ($field_related_datasets as $dataset_id){
    $id = $dataset_id['value'];
    $api = $spc_base_url . 'api/3/action/package_show';

    if ($responce = json_decode(file_get_contents($api . '?id=' . $id))){
      $publication = $responce->result;

      if (is_object($publication)){
        $dataset = [];
        $dataset['name'] = $publication->name;
        $dataset['title'] = $publication->title;
        $dataset['url'] = $spc_base_url . 'dataset/' . $publication->id;

        //creation date.
        $originalDate = $publication->metadata_created;
        $dataset['date'] = date("M, d, Y", strtotime($originalDate));

        //Organisation data.
        $dataset['organization']['image_url'] = $spc_base_url . 'uploads/group/' . $publication->organization->image_url;
        $dataset['organization']['spc_base_url'] = $spc_base_url . 'organization/' . $publication->organization->name;
        $dataset['organization']['title'] = $publication->organization->title;

        //Category mapper.
        $tags = $publication->tags;
        $categories =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('categories');

        foreach ($categories as $category_term) {
          $category = Term::load($category_term->tid);
          $term_code = $category->get('field_c_code')->getValue()[0]['value'];

          foreach ($tags as $tag){
            $tag_name = $tag->name;

            if ($term_code == $tag_name){
              $aliasManager = \Drupal::service('path_alias.manager');
              $url = $aliasManager->getAliasByPath('/taxonomy/term/' . $category->id());

              $dataset['tags'][] = [
                'url' => $url,
                'name' => $category->getName(),
              ];
            }

          }
        }

        //Resources data.
        if ($resources = $publication->resources){
            foreach ($resources as $resource){
                $data_resource['url'] = $resource->url;
                $data_resource['format'] = $resource->format;
                $dataset['resources'][$resource->format] = $data_resource;
            }
        }

        $detales[] = $dataset;
      }
    }
  }
  return $detales;
}

function _get_country_related_datasets($variables){
    $country = $variables['elements']['#taxonomy_term'];
    $c_code = $country->get('field_country_code')->getValue()[0]['value'];
    $dataset_ids = $country->get('field_related_datasets')->getValue();

    $spc_base_url = 'https://pacificdata.org/data/';

    $country_detales = [];
    $country_detales['datasetsCount'] = 0;

    if (!empty($dataset_ids)){
        foreach ($dataset_ids as $dataset_id){
            $id = $dataset_id['value'];
            $api = $spc_base_url . 'api/3/action/package_show';
            
            if ($responce = json_decode(file_get_contents($api . '?id=' . $id))){
                $publication = $responce->result;

                if (is_object($publication)){
                    $country_detales['datasetsCount'] += 1;

                    $dataset = [];
                    $dataset['name'] = $publication->name;
                    $dataset['title'] = $publication->title;
                    $dataset['url'] = $spc_base_url . 'dataset/' . $publication->id;

                    //creation date.
                    $originalDate = $publication->metadata_created;
                    $dataset['date'] = date("M, d, Y", strtotime($originalDate));

                    //Organisation data.
                    $dataset['organization']['image_url'] = 'https://pacificdata.org/data/uploads/group/' . $publication->organization->image_url;
                    $dataset['organization']['spc_base_url'] = $spc_base_url . 'organization/' . $publication->organization->name;
                    $dataset['organization']['title'] = $publication->organization->title;

                    //Category mapper.
                    $tags = $publication->tags;
                    $categories =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('categories');

                    foreach ($categories as $category_term) {
                      $category = Term::load($category_term->tid);
                      $term_code = $category->get('field_c_code')->getValue()[0]['value'];

                      foreach ($tags as $tag){
                        $tag_name = $tag->name;

                        if ($term_code == $tag_name){
                          $aliasManager = \Drupal::service('path_alias.manager');
                          $url = $aliasManager->getAliasByPath('/taxonomy/term/' . $category->id());

                          $dataset['tags'][] = [
                            'url' => $url,
                            'name' => $category->getName(),
                          ];
                        }

                      }
                    }

                    //Resources data.
                    if ($resources = $publication->resources){
                        foreach ($resources as $resource){
                            $data_resource['url'] = $resource->url;
                            $data_resource['format'] = $resource->format;
                            $dataset['resources'][$resource->format] = $data_resource;
                        }
                    }

                    $country_detales['datasets'][] =  $dataset;
                }
            }
        }
    }

    return $country_detales;
}

function _get_countries_list($current_country){
    $countries = [];
    
    $current_code = $current_country->get('field_country_code')->getValue()[0]['value'];

    $countries_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('country');
    $theme = \Drupal::theme()->getActiveTheme();
    $theme_path = $theme->getPath();

    $active_key = 0;
    foreach($countries_terms as $key => $country_term){
        $country = Term::load($country_term->tid);

        $name = $country->getName();
        $country_code = $country->get('field_country_code')->getValue()[0]['value'];

        $aliasManager = \Drupal::service('path_alias.manager');
        $url = $aliasManager->getAliasByPath('/taxonomy/term/' . $country->id());
        
        $class_name = '';
        if ($current_code == $country_code){
            $class_name = 'active';
            $active_key = $key;
        }

        $countries['list'][] = [
          'url' => $url,
          'name' => $name,
          'class' => $class_name,  
        ];
      }
      
      $countries['current'] = $countries['list'][$active_key];
      if ($active_key == count($countries_terms) -1){
          $countries['prev'] = $countries['list'][count($countries_terms) -2];
          $countries['next'] = $countries['list'][0];
      } else if ($active_key == 0){
          $countries['prev'] = $countries['list'][count($countries_terms) -1];
          $countries['next'] = $countries['list'][1];
      } else {
          $countries['prev'] = $countries['list'][$active_key -1];
          $countries['next'] = $countries['list'][$active_key +1];
      }

    return $countries;
}

function _get_country_treaty($term){
    $output = [
        'completed' => [],
        'in_progress' => [],
        'not_started' => [],
        'na' => []
    ];
    
    $country_id = intval($term->id());
    $condition_or = new \Drupal\Core\Database\Query\Condition('OR');
    $condition_or->condition('bco.field_boundary_country_one_target_id', $country_id);
    $condition_or->condition('bct.field_boundary_country_two_target_id', $country_id);
    
    $q = \Drupal::database()->select('node','n')
        ->fields('n', ['nid'])
        ->condition('n.type', 'boundary_treaty')
        ->condition($condition_or);
    $q->join('node__field_boundary_country_one', 'bco',  'bco.entity_id = n.nid');
    $q->join('node__field_boundary_country_two', 'bct',  'bct.entity_id = n.nid');
    $results = $q->execute()->fetchAll();
    
    $theme = \Drupal::theme()->getActiveTheme();
    $theme_path = $theme->getPath();
    
    if (!empty($results)){
        foreach($results as $key => $value){
            $treaty = Node::load($value->nid);
            
            $treaty_state = $treaty->get('field_treaty_state')->getValue()[0]['value'];
            $output[$treaty_state][$key]['state'] = $treaty_state;

            $country_one_id = $treaty->get('field_boundary_country_one')->getValue()[0]['target_id'];
            $country_one = Term::load($country_one_id);
            $output[$treaty_state][$key]['country_one'] = $country_one->getName();
            $country_one_url = \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $country_one_id);
            $output[$treaty_state][$key]['country_one_url'] = $country_one_url;

            $country_one_file = File::load($country_one->get('field_flag')->getValue()[0]['target_id']);
            if (is_object($country_one_file)){
              $output[$treaty_state][$key]['country_one_flag'] = file_create_url($country_one_file->getFileUri());
            } else {
                $country_one_flag = '/' . $theme_path . '/img/flags/' . $country_one->get('field_country_code')->getValue()[0]['value'] . '.svg';
                $output[$treaty_state][$key]['country_one_flag'] = $country_one_flag;
            }
            
            $country_two_id = $treaty->get('field_boundary_country_two')->getValue()[0]['target_id'];
            $country_two = Term::load($country_two_id);
            $output[$treaty_state][$key]['country_two'] = $country_two->getName();
            $country_two_url = \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/' . $country_two_id);
            $output[$treaty_state][$key]['country_two_url'] = $country_two_url;            
            
            $country_two_file = File::load($country_two->get('field_flag')->getValue()[0]['target_id']);
            if (is_object($country_two_file)){
                $output[$treaty_state][$key]['country_two_flag'] = file_create_url($country_two_file->getFileUri());
            } else {
                $country_two_flag = '/' . $theme_path . '/img/flags/' . $country_two->get('field_country_code')->getValue()[0]['value'] . '.svg';
                $output[$treaty_state][$key]['country_two_flag'] = $country_two_flag;
            }

            $download = File::load($treaty->get('field_boundaries_treaty_file')->getValue()[0]['target_id']);
            if (is_object($download)){
              $output[$treaty_state][$key]['download'] = file_create_url($download->getFileUri());
            }
            
            switch ($treaty_state) {
                case 'completed': 
                    $completed_date = $treaty->get('field_year_only')->getValue()[0]['value'];
                    $output[$treaty_state][$key]['completed_date'] = $completed_date;
                    
                    break;
                case 'in_progress': 
                    $steps = $treaty->get('field_boundaries_treaty_steps')->getValue();
                    $treaty_steps = [];
                    foreach($steps as $step){
                        $paragraph_step = Paragraph::load($step['target_id']);
                        $state = $paragraph_step->field_progress_type->value;

                        $step_term_id = $paragraph_step->field_boundary_treaty->getValue()[0]['target_id'];
                        $paragraph_treaty = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($step_term_id);
                        $name = $paragraph_treaty->label();
                        
                        $icon = '';
                        $file = File::load($paragraph_treaty->get('field_image')->getValue()[0]['target_id']);
                        if (is_object($file)){
                          $icon = file_create_url($file->getFileUri());
                        }

                        $treaty_steps[] = [
                            'state' => $state,
                            'name' => $name,
                            'icon' => $icon,
                            'tooltip' => ucfirst(str_replace('_', ' ', $state)),
                        ];
                    }
                    
                    $output[$treaty_state][$key]['treaty_steps'] = $treaty_steps;
                    break;                
            }
        }
    }
    
    return $output;
}

function _get_shelf_treaty($term){
    $output = [];
    
    $country_id = intval($term->id());
    $q = \Drupal::database()->select('node','n')
        ->fields('n', ['nid'])
        ->condition('n.type', 'continental_shelf')
        ->condition('lc.field_limit_countries_target_id', $country_id);
    $q->join('node__field_limit_countries', 'lc',  'lc.entity_id = n.nid');
    $results = $q->execute()->fetchAll();
    
    $theme = \Drupal::theme()->getActiveTheme();
    $theme_path = $theme->getPath(); 
    
    if (!empty($results)){
        foreach($results as $key => $value){
            $shelf = [];
            
            $shelf_node = Node::load($value->nid);             
            $shelf['name'] = $shelf_node->get('title')->getValue()[0]['value'];
            
            $steps = $shelf_node->get('field_shelf_steps')->getValue();
            $treaty_steps = [];
            foreach($steps as $step){
                $paragraph_step = Paragraph::load($step['target_id']);
                $state = $paragraph_step->field_progress_type->value;

                $step_term_id = $paragraph_step->field_shelf_step_term->getValue()[0]['target_id'];
                $paragraph_treaty = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($step_term_id);
                $name = $paragraph_treaty->label();

                $icon = '';
                $file = File::load($paragraph_treaty->get('field_image')->getValue()[0]['target_id']);
                if (is_object($file)){
                  $icon = file_create_url($file->getFileUri());
                }

                $treaty_steps[] = [
                    'state' => $state,
                    'name' => $name,
                    'icon' => $icon,
                    'tooltip' => ucfirst(str_replace('_', ' ', $state)),
                ];
            }
            $shelf['steps'] = $treaty_steps;
            
            $output[] = $shelf;
        }
    }            
    return $output;
}

function _get_country_map_data($term){
    $country_code = $term->get('field_country_code')->getValue()[0]['value'];
    //$filespath = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");

    $SpcMbdController = new SpcMbdController();
    $map_data = $SpcMbdController->get_map_data();    

    $additional_data = set_country_eez_json($country_code, $SpcMbdController);
    set_country_boundaries_json($country_code, $SpcMbdController);
    set_country_limits_json($country_code, $SpcMbdController);       
    set_country_shelf_json($country_code, $SpcMbdController);
    
    $map['eez']['eez-' . $country_code] = $map_data['eez']['eez-' . $country_code];
    $map['limits'] = $map_data['limits'];
    $map['shelf'] = $map_data['shelf'];
    $map['boundary'] = $map_data['boundary'];
    $map['additional'] = $additional_data;
    
    $file = File::load($term->get('field_flag')->getValue()[0]['target_id']);
    if (is_object($file)){
      $flag = file_create_url($file->getFileUri());
    }  

    $map['country'] = [
        'name' => $term->getName(),
        'flag' => $flag, 
        'code' => $country_code
    ];

    return $map;
}

function set_country_boundaries_json($country_code, $SpcMbdController){
    $q = \Drupal::database()->select('node','n')
        ->fields('n', ['nid'])
        ->condition('n.type', 'boundary_treaty');
    $results = $q->execute()->fetchAll();
    
    if (!empty($results)){
        $treatyjson = '';
        
        foreach($results as $key => $value){
          $node = Node::load($value->nid);

          $tid_one = $node->get('field_boundary_country_one')->getValue()[0]['target_id'];
          $term_one = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid_one);

          $tid_two = $node->get('field_boundary_country_two')->getValue()[0]['target_id'];
          $term_two = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid_two);
          if ($term_one->get('field_country_code')->value == $country_code || $term_two->get('field_country_code')->value == $country_code){

            if ($line_json = @$node->get('field_geojson_coordinates')->getValue()[0]['value']){
              $line_array = json_decode($line_json, true);
              $geo_item = [];

              if (!array_key_exists('id', $line_array)){
                $geo_item['id'] = 'boundary-' . $value->nid;
                $geo_item['feature'] = $line_array;
              } else {
                $line_array['id'] = 'boundary-' . $value->nid;
                $geo_item = $line_array;
              }

              if ($state = $node->get('field_treaty_state')->getValue()[0]['value']){
                  foreach ($geo_item['feature']['features'] as $fid => $feature){
                    $geo_item['feature']['features'][$fid]['properties']['stroke'] = $SpcMbdController->states_colors[$state];
                    $geo_item['feature']['features'][$fid]['properties']['stroke-width'] = $SpcMbdController->boundary_colors['stroke-width'];
                    $geo_item['feature']['features'][$fid]['properties']['stroke-opacity'] = $SpcMbdController->boundary_colors['stroke-opacity'];
                  }
              }

              $treatyjson .= json_encode($geo_item) . ',';
            }
          }
        }
        if ($treatyjson != ''){
          $treatyjson =  '[' . substr($treatyjson, 0, -1) . ']';
          create_json_file('boundaries-'. $country_code.'.json', $treatyjson ); 
        } else {
      create_json_file('boundaries-'. $country_code.'.json', '' ); 
    }

    } else {
      create_json_file('boundaries-'. $country_code.'.json', '' ); 
    }
}

function set_country_limits_json($country_code, $SpcMbdController){
    $q = \Drupal::database()->select('node','n')
    ->fields('n', ['nid'])
    ->condition('n.type', 'high_seas_limit');
    $results = $q->execute()->fetchAll();

    if (!empty($results)){
        $geojson = '';

        foreach($results as $key => $value){
            $limit = [];
            $node = Node::load($value->nid);
            $tid = $node->get('field_limit_countries')->getValue()[0]['target_id'];
            if (@$term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid)){
              if ($term->get('field_country_code')->value == $country_code) {
                  if ($line_json = $node->get('field_geojson_coordinates')->getValue()[0]['value']){
                    $line_array = json_decode($line_json, true);
                    $geo_item = [];

                    if (!array_key_exists('id', $line_array)){
                      $geo_item['id'] = 'limit-' . $value->nid;
                      $geo_item['feature'] = $line_array;
                    } else {
                      $line_array['id'] = 'limit-' . $value->nid;
                      $geo_item = $line_array;
                    }

                    foreach ($geo_item['feature']['features'] as $fid => $feature){
                      $geo_item['feature']['features'][$fid]['properties']['stroke'] = $SpcMbdController->limit_colors['stroke'];
                      $geo_item['feature']['features'][$fid]['properties']['stroke-width'] = $SpcMbdController->limit_colors['stroke-width'];
                      $geo_item['feature']['features'][$fid]['properties']['stroke-opacity'] = $SpcMbdController->limit_colors['stroke-opacity'];
                    } 

                    $geojson .= json_encode($geo_item) . ',';
                  }
              }
            }
        }
        
        if ($geojson !== ''){
          $geojson =  '[' . substr($geojson, 0, -1) . ']';
          create_json_file('limits-'. $country_code.'.json', $geojson );
        } else {
        create_json_file('limits-'. $country_code.'.json', '' ); 
    }
 
    } else {
        create_json_file('limits-'. $country_code.'.json', '' ); 
    }
}

function set_country_shelf_json($country_code, $SpcMbdController){
    $q = \Drupal::database()->select('node','n')
        ->fields('n', ['nid'])
        ->condition('n.type', 'continental_shelf');
    $results = $q->execute()->fetchAll();    
    
    if (!empty($results)){
      $geojson = '';
      foreach($results as $key => $value){
        $limit = [];
        $node = Node::load($value->nid);
        $tid = @$node->get('field_limit_countries')->getValue()[0]['target_id'];
        
        if (@$term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid)){
          if ($term->get('field_country_code')->value == $country_code) {
            if ($line_json = @$node->get('field_geojson_coordinates')->getValue()[0]['value']){
              $line_array = json_decode($line_json, true);
              $geo_item = [];

              if (!array_key_exists('id', $line_array)){
                $geo_item['id'] = 'shelf-' . $value->nid;
                $geo_item['feature'] = $line_array;
              } else {
                $line_array['id'] = 'shelf-' . $value->nid;
                $geo_item = $line_array;
              }

              foreach ($geo_item['feature']['features'] as $fid => $feature){
                $geo_item['feature']['features'][$fid]['properties']['stroke'] = $SpcMbdController->shelf_colors['stroke'];
                $geo_item['feature']['features'][$fid]['properties']['stroke-width'] = $SpcMbdController->shelf_colors['stroke-width'];
                $geo_item['feature']['features'][$fid]['properties']['stroke-opacity'] = $SpcMbdController->shelf_colors['stroke-opacity'];
                $geo_item['feature']['features'][$fid]['properties']['fill'] = $SpcMbdController->shelf_colors['fill'];
                $geo_item['feature']['features'][$fid]['properties']['fill-opacity'] = $SpcMbdController->shelf_colors['fill-opacity'];
              }

              $geojson .= json_encode($geo_item) . ','; 
            }
          }
        }
      } 
      if ($geojson != ''){
      $geojson =  substr($geojson, 0, -1) . ']';
      create_json_file('shelf-'. $country_code.'.json', $geojson );
      }else {
      create_json_file('shelf-'. $country_code.'.json', '' );
    }

    } else {
      create_json_file('shelf-'. $country_code.'.json', '' );
    }
 
}

function set_country_eez_json($country_code, $SpcMbdController){
    $geojson = '[';
    
    $countries_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('country'); 
    foreach($countries_tax as $country_term){
      $term = Term::load($country_term->tid);
      $plygon_json = @$term->get('field_eez_plygon')->getValue()[0]['value'];
       
      if ($SpcMbdController->is_json($plygon_json)){
          $current_country_code = $term->get('field_country_code')->getValue()[0]['value'];

        if ($term->get('field_country_code')->value == $country_code) {
          if ($current_country_code == 'KI'){
            $ki_polygons = json_decode($plygon_json, true);
            foreach ($ki_polygons['KI'] as $key => $ki_polygon){
              
              if (!array_key_exists('feature', $ki_polygon)){
                $ki_polygon['feature'] = $ki_polygon;
              }       

              foreach ($ki_polygon['feature']['features'] as $fid => $feature){
                $ki_polygon['feature']['features'][$fid]['properties']['stroke'] = $SpcMbdController->eez_colors['stroke'];
                $ki_polygon['feature']['features'][$fid]['properties']['stroke-width'] = $SpcMbdController->eez_colors['stroke-width'];
                $ki_polygon['feature']['features'][$fid]['properties']['stroke-opacity'] = 0.5;
                $ki_polygon['feature']['features'][$fid]['properties']['fill'] = $SpcMbdController->eez_colors['fill'];
                $ki_polygon['feature']['features'][$fid]['properties']['fill-opacity'] = 0.5;
              }

              $ki_polygon['id'] = 'eez-' . $current_country_code . $key;
              $geojson .= json_encode($ki_polygon) . ',';
            }
          } else {
              $plygon_array = json_decode($plygon_json, true);
              
              if (!array_key_exists('feature', $plygon_array)){
                $plygon_array['feature'] = $plygon_array;
              }       

              foreach ($plygon_array['feature']['features'] as $fid => $feature){
                $plygon_array['feature']['features'][$fid]['properties']['stroke'] = $SpcMbdController->eez_colors['stroke'];
                $plygon_array['feature']['features'][$fid]['properties']['stroke-width'] = $SpcMbdController->eez_colors['stroke-width'];
                $plygon_array['feature']['features'][$fid]['properties']['stroke-opacity'] = 0.5;
                $plygon_array['feature']['features'][$fid]['properties']['fill'] = $SpcMbdController->eez_colors['fill'];
                $plygon_array['feature']['features'][$fid]['properties']['fill-opacity'] =  0.5;
              }              

              $plygon_array['id'] = 'eez-' . $current_country_code;
              $geojson .= json_encode($plygon_array) . ',';
          }
        } else {
          if ($current_country_code == 'KI'){
            $ki_polygons = json_decode($plygon_json, true);
            foreach ($ki_polygons['KI'] as $key => $ki_polygon){
              

              if (!array_key_exists('feature', $ki_polygon)){
                $ki_polygon['feature'] = $ki_polygon;
              }       

              foreach ($ki_polygon['feature']['features'] as $fid => $feature){
                $ki_polygon['feature']['features'][$fid]['properties']['stroke'] = $SpcMbdController->eez_colors['stroke'];
                $ki_polygon['feature']['features'][$fid]['properties']['stroke-width'] = $SpcMbdController->eez_colors['stroke-width'];
                $ki_polygon['feature']['features'][$fid]['properties']['stroke-opacity'] = 0.2;
                $ki_polygon['feature']['features'][$fid]['properties']['fill'] = $SpcMbdController->eez_colors['fill'];
                $ki_polygon['feature']['features'][$fid]['properties']['fill-opacity'] = 0.2;
              }

              $ki_polygon['id'] = 'eez-' . $current_country_code . $key;
              $geojson .= json_encode($ki_polygon) . ',';
            }
          } else {
              $plygon_array = json_decode($plygon_json, true);
              
              if (!array_key_exists('feature', $plygon_array)){
                $plygon_array['feature'] = $plygon_array;
              }       

              foreach ($plygon_array['feature']['features'] as $fid => $feature){
                $plygon_array['feature']['features'][$fid]['properties']['stroke'] = $SpcMbdController->eez_colors['stroke'];
                $plygon_array['feature']['features'][$fid]['properties']['stroke-width'] = $SpcMbdController->eez_colors['stroke-width'];
                $plygon_array['feature']['features'][$fid]['properties']['stroke-opacity'] = 0.2;
                $plygon_array['feature']['features'][$fid]['properties']['fill'] = $SpcMbdController->eez_colors['fill'];
                $plygon_array['feature']['features'][$fid]['properties']['fill-opacity']  = 0.2;
              } 

              $plygon_array['id'] = 'eez-' . $current_country_code;
              $geojson .= json_encode($plygon_array) . ',';
          }
        }
      }
      
      $additional_data_list = [
        'baseline' => [
            'json' => 'field_eez_sea_baseline',
            'popup' => 'field_eez_sea_baseline_popup',
        ],
        'seelim' => [
            'json' => 'field_eez_sea_limit',
            'popup' => 'field_eez_sea_limit_popup',
        ],
        'marine' => [
            'json' => 'field_eez_marine_protected_areas',
            'popup' => 'field_eez_marine_areas_popup',
        ],
        'contiguous' => [
            'json' => 'field_eez_contiguous_zone',
            'popup' => 'field_eez_contiguous_zone_popup',
        ],
        'archipelagic' => [
            'json' => 'field_eez_archipelagic_baseline',
            'popup' => 'field_eez_archipelagic_popup',
        ]          
      ];
      
      foreach ($additional_data_list as $key => $field){
        if ($term->get('field_country_code')->value == $country_code){
          $additional_data[$key] = set_additional_json_data($term, $country_code, $SpcMbdController, $key, $field);
        }
      }
    }
    $geojson =  substr($geojson, 0, -1) . ']';
    create_json_file('eez-'. $country_code.'.json', $geojson ); 
    
    return $additional_data;
}

function create_json_file($filename, $json){
    $file = File::create([
      'uid' => 1,
      'filename' => $filename,
      'uri' => 'public://mbd/'. $filename,
      'status' => 1,
    ]);
    $file->save();

    $dir = dirname($file->getFileUri());
    if (!file_exists($dir)) {
      mkdir($dir, 0777, TRUE);
    }

    file_put_contents($file->getFileUri(), $json);
    $file->save();
}

function set_additional_json_data($term, $country_code, $SpcMbdController, $key, $field){
  
  $json = @$term->get($field['json'])->getValue()[0]['value'];
  if ($SpcMbdController->is_json($json)){
      $geoarray = json_decode($json, true);
      $geo_item = [];
    
      if (!array_key_exists('id', $geoarray)){
        $geo_item['id'] = $key . '-' . $country_code;
        $geo_item['feature'] = $geoarray;
      } else {
        $line_array['id'] = $key . '-' . $country_code;
        $geo_item = $geoarray;
      }
      
      foreach ($geo_item['feature']['features'] as $fid => $feature){
        $geo_item['feature']['features'][$fid]['properties']['stroke'] = $SpcMbdController->aditional_colors[$key]['stroke'];
        $geo_item['feature']['features'][$fid]['properties']['stroke-width'] = $SpcMbdController->aditional_colors[$key]['stroke-width'];
        $geo_item['feature']['features'][$fid]['properties']['stroke-opacity'] = $SpcMbdController->aditional_colors[$key]['stroke-opacity'];
        $geo_item['feature']['features'][$fid]['properties']['fill'] = $SpcMbdController->aditional_colors[$key]['fill'];
        $geo_item['feature']['features'][$fid]['properties']['fill-opacity'] = $SpcMbdController->aditional_colors[$key]['fill-opacity'];
      }

      $geojson = json_encode($geo_item) . ','; 
      if (!empty($geojson)){
        $geojson =  '[' . substr($geojson, 0, -1) . ']';
        create_json_file($key . '-'. $country_code.'.json', $geojson );
      }
  } else {
    create_json_file($key . '-'. $country_code.'.json', '' );
  }
  
  $popup_id = @$term->get($field['popup'])->getValue()[0]['target_id'];
  if ($popup_id){
    $paragraph = Paragraph::load($popup_id);
      
    $popup_data = [
      'area' => $paragraph->field_area->value ? number_format($paragraph->field_area->value) : '-',
      'legislated' => $paragraph->field_legislated->value ? 'Yes' : 'No' ,
      'legislatedDate' => $paragraph->field_date_legislated->value,
      'deposited' => $paragraph->field_deposited->value ? 'Yes' : 'No',
      'depositedDate' => $paragraph->field_date_deposited->value,
      'url' => $paragraph->field_url->value, 
    ];
  
    if ($related_datasets = _get_additional_zones_datasets($paragraph->get('field_related_datasets')->getValue())){
      $popup_data['datasets'] = $related_datasets;
    }
  }  
  
  return $popup_data;
}

function _get_additional_zones_datasets($dataset_ids){
  $spc_base_url = 'https://pacificdata.org/data/';

  $related_datasets = [];
  if (!empty($dataset_ids)){
      foreach ($dataset_ids as $dataset_id){
          $id = $dataset_id['value'];
          $api = $spc_base_url . 'api/3/action/package_show';

          if ($responce = json_decode(file_get_contents($api . '?id=' . $id))){
              $publication = $responce->result;

              if (is_object($publication)){
                  $dataset = [];
                  $dataset['name'] = $publication->name;
                  $dataset['title'] = $publication->title;
                  $dataset['url'] = $spc_base_url . 'dataset/' . $publication->id;

                  //creation date.
                  $originalDate = $publication->metadata_created;
                  $dataset['date'] = date("M, d, Y", strtotime($originalDate));

                  //Organisation data.
                  $dataset['organization']['img'] = 'https://pacificdata.org/data/uploads/group/' . $publication->organization->image_url;
                  $dataset['organization']['spc_base_url'] = $spc_base_url . 'organization/' . $publication->organization->name;
                  $dataset['organization']['title'] = $publication->organization->title;

                  //Category mapper.
                  $tags = $publication->tags;
                  $categories =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('categories');

                  foreach ($categories as $category_term) {
                    $category = Term::load($category_term->tid);
                    $term_code = $category->get('field_c_code')->getValue()[0]['value'];

                    foreach ($tags as $tag){
                      $tag_name = $tag->name;

                      if ($term_code == $tag_name){
                        $aliasManager = \Drupal::service('path_alias.manager');
                        $url = $aliasManager->getAliasByPath('/taxonomy/term/' . $category->id());

                        $dataset['tags'][] = [
                          'url' => $url,
                          'name' => $category->getName(),
                        ];
                      }

                    }
                  }

                  //Resources data.
                  if ($resources = $publication->resources){
                      $dataset['resources'] = [];
                      foreach ($resources as $resource){
                          $data_resource['url'] = $resource->url;
                          $data_resource['format'] = $resource->format;
                          $dataset['resources'][] = $data_resource;
                      }
                  }

                  $related_datasets[] =  $dataset;
              }
          }
      }
      
      return $related_datasets;
  }
}
