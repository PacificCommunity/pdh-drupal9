<?php

namespace Drupal\spc_main\Controller;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\image\Entity\ImageStyle;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Controller\ControllerBase;

class SpcHomeController  extends ControllerBase {
  
  const DATA_BASE_PROTOCOL = 'https';
  
  const DATA_BASE_DOMAIN = 'pacificdata.org';

  public function mainLanding() {

    $data['stats'] = $this->get_home_stats();
    $data['dashboards'] = $this->get_home_dashboards();
    $data['countries'] = $this->get_members_countries();
    $data['stories'] = $this->get_home_stories();
    $data['datasets'] = $this->get_home_datasets();
    $data['quotes'] = $this->get_home_quotes();
    
    return [
      '#theme' => 'spc_home_page',
      '#data' => $data,
      '#attached' => [
        'library' => [
          'spc/gmap',
        ],
      ],        
    ];
  }
  
  public function get_home_stats() {
    
    $stats['datasets'] = $this->_ckan_dataset_count();
    $stats['publications'] = $this->_ckan_publications_count();
    $stats['organizations'] = $this->_ckan_organisations_count(); 
    
    return $stats;
  }
  
  public function get_home_dashboards(){
    $dashboards = [];
    
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)   
      ->condition('type', 'dashboards')
      ->pager(10);
    $nids = $query->execute();

    foreach ($nids as $nid) {
      $dashboard = [];
      $node = \Drupal\node\Entity\Node::load($nid);

      if ($node->field_preview->value){
        $dashboard['url'] = $node->field_url->value;
        $dashboard['title'] = $node->field_dsp_title_markup->value;
        $dashboard['body'] = $node->field_data_insights_preview->value;
        $dashboard['weight'] = $node->field_dsp_view_weight->value;

        if (is_object($node->field_image->entity)){
          $style = ImageStyle::load('medium');
          $styled_image_url = $style->buildUrl($node->field_image->entity->getFileUri());
          $dashboard['img'] = $styled_image_url;
        }
        
        $dashboards[$dashboard['weight']] = $dashboard;        
      }
    }  
    ksort($dashboards);
    
    return $dashboards;
  }
  
  public function get_members_countries(){
    $countries = [];

    $spc_base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
    $countries_tax =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('country');
    $theme = \Drupal::theme()->getActiveTheme();
    $theme_path = $theme->getPath();
    
    $total_datasets_count = 0;
    $spcPublicationImport = \Drupal::service('spc_publication_import.spcPublicationImport');   
    foreach($countries_tax as $country_term){
        $country = Term::load($country_term->tid);

        $name = $country->getName();
        $country_code = $country->get('field_country_code')->getValue()[0]['value'];
        $datasets_count = @$country->get('field_datasets_count')->getValue()[0]['value'];

        if (!$datasets_count){
          $datasets_count = $spcPublicationImport->get_ckan_country_datasets_count($country_code);
          $country->set('field_datasets_count', $datasets_count);
          $country->save();
        }
        $total_datasets_count += $datasets_count;

        $aliasManager = \Drupal::service('path_alias.manager');
        $url = $aliasManager->getAliasByPath('/taxonomy/term/' . $country->id());

        $countries[] = [
          'url' => $spc_base_url . 'dataset?member_countries=' . $country_code,
          'code' => $country_code,
          'name' => $name,
          'datasets' => $datasets_count
        ];
      }

    return [
        'list' => $countries,
        'total_datasets' => $total_datasets_count
    ] ;
  }
  
  public function get_home_stories(){
    $stories = [];
    
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'article')
      ->sort('created' , 'DESC')
      ->pager(10);
    $nids = $query->execute();

    foreach ($nids as $nid) {
      $story = [];
      $node = \Drupal\node\Entity\Node::load($nid); 
      $story['url'] = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid);
      $story['title'] = $node->title->value;
      
      $style = ImageStyle::load('stories_slides');
      $styled_image_url = $style->buildUrl($node->field_image->entity->getFileUri());
      $story['img'] = $styled_image_url;
      
      $stories[] = $story;
    }  
    
    return $stories;
  }
  
  public function get_home_datasets() {
    $datasets = [];
    
    $datasets['latest'] = $this->_ckan_datasets('latest');
    $datasets['popular'] = $this->_ckan_datasets('popular');
    $datasets['url'] = self::DATA_BASE_PROTOCOL . '://' . self::DATA_BASE_DOMAIN . '/data';
    
    return $datasets;
  }
  
  public function _ckan_datasets($sort) {
    $base_url = self::DATA_BASE_PROTOCOL . '://' . self::DATA_BASE_DOMAIN;
    
    //Get latest cached publications.
    $cached_publications = \Drupal::config('spc_publication_import.settings')->get('spc_publications_' . $sort);

    if (empty($cached_publications)){
      //Get new publications from API.
      $spcPublicationImport = \Drupal::service('spc_publication_import.spcPublicationImport');
      $publications = $spcPublicationImport->get_ckan_datasets($sort);

      if ($publications){
        \Drupal::configFactory()->getEditable('spc_publication_import.settings')
          ->set('spc_publications_' . $sort, json_encode($publications))
          ->save();
      }    
    } else {
      $publications = json_decode($cached_publications);
    }

    foreach($publications as $key => $publication){
      $dataset = [];
      $dataset['name'] = $publication->name;
      $dataset['title'] = $publication->title;
      $dataset['url'] = $base_url . '/data/dataset/' . $publication->id;

      //creation date.
      $originalDate = $publication->metadata_created;
      $dataset['date'] = date("F, d, Y", strtotime($originalDate));

      //Organisation data.
      if (filter_var($publication->organization->image_url, FILTER_VALIDATE_URL) !== false){
          $dataset['organization']['image_url'] = $publication->organization->image_url;
      } else {
          $dataset['organization']['image_url'] = $base_url . '/data/uploads/group/'. $publication->organization->image_url;
      }

      $dataset['organization']['url'] = $base_url . '/data/organization/' . $publication->organization->name;
      $dataset['organization']['title'] = $publication->organization->title;

      //Resources data.
      if ($resources = $publication->resources){
          foreach ($resources as $resource){
              $data_resource['url'] = $resource->url;
              $data_resource['format'] = strtolower($resource->format);
              $dataset['resources'][$resource->format] = $data_resource;
          }
      }

      $datasets[] = $dataset;
    }

    return $datasets;
  }
  
  public function _ckan_dataset_count() {
    $base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');

    $res_formats = [
        'CSV','XML', 'XLS', 'XLSX', 'ODS', 'MDB', 'MDE', 'DBF',
        'SQL', 'SQLITE', 'DB', 'DBF', 'DBS', 'ODB', 'JSON', 
        'GEOJSON', 'KML', 'KMZ', 'SHP', 'SHX', 'WMS', 'WFS', 'WCS', 'CSW'];
    $attr = ['dataset', 'service'];
    $type = ['dataset', 'biodiversity_data'];

    $query = '?q=';
    $params = urlencode('res_format:('. implode(' OR ', $res_formats) .')');
    $params .= urlencode(' OR dcat_type:('. implode(' OR ', $attr) .')');
    $params .= urlencode(' OR type:('. implode(' OR ', $type) .')');     
    
    $cached_dataset_count = \Drupal::config('spc_publication_import.settings')->get('spc_datasets_count');
    if ($cached_dataset_count) {
      $count = $cached_dataset_count;
    } else {   
      //Get new count from API.
      $spcPublicationImport = \Drupal::service('spc_publication_import.spcPublicationImport');
      $count = $spcPublicationImport->get_ckan_dataset_count();
      if ($count){
        \Drupal::configFactory()->getEditable('spc_publication_import.settings')
          ->set('spc_datasets_count', $count)
          ->save();
      }
    }

    $description = 'Structured and semi-structured data, spatial data, links to online databases and web services';
    $solorq = '&ext_advanced_type=solr&ext_advanced_operator=or';

    return [
      'count' => $count,
      'url' =>  $base_url . 'dataset?general_type=Datasets',
      'title' => 'Datasets',
      'description' => $description,
    ];
  }  
  
  public function _ckan_publications_count() {
    $base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');

    $res_formats = ['PDF','DOC', 'DOCX', 'ODF', 'ODT', 'EPUB', 'MOBI'];
    $attr = ['text'];
    $type = ['publications'];
    
    $query = '?q=';
    $params = urlencode('res_format:('. implode(' OR ', $res_formats) .')');
    $params .= urlencode(' OR dcat_type:('. implode(' OR ', $attr) .')');
    $params .= urlencode(' OR type:('. implode(' OR ', $type) .')');
    
    $cached_publications_count = \Drupal::config('spc_publication_import.settings')->get('spc_publications_count');
    if ($cached_publications_count) {
      $count = $cached_publications_count;
    } else {
      //Get new count from API.
      $spcPublicationImport = \Drupal::service('spc_publication_import.spcPublicationImport');
      $count = $spcPublicationImport->get_ckan_publications_count();
      if ($count){
        \Drupal::configFactory()->getEditable('spc_publication_import.settings')
          ->set('spc_publications_count', $count)
          ->save();        
      } 
    }
    
    $description = 'Scientific papers, publications, reports, policy briefs, policies documents, manuals, handbooks';
    $solorq = '&ext_advanced_type=solr&ext_advanced_operator=or';

    return [
      'count' => $count,
      'url' =>  $base_url . 'dataset?general_type=Publications',
      'title' => 'Publications',
      'description' => $description,
    ];
  }
  
  public function _ckan_organisations_count() {
    $base_url = self::DATA_BASE_PROTOCOL . '://' . self::DATA_BASE_DOMAIN;
    $organizations_url = $base_url . '/data/api/action/organization_list';
    
    $cached_organization_count = \Drupal::config('spc_publication_import.settings')->get('spc_organisations_count');
    if ($cached_organization_count){
      $count = $cached_organization_count;
    } else {
      //Get new count from API.
      $spcPublicationImport = \Drupal::service('spc_publication_import.spcPublicationImport');
      $count = $spcPublicationImport->get_ckan_organisations_count();
      if ($count){
        \Drupal::configFactory()->getEditable('spc_publication_import.settings')
          ->set('spc_organisations_count', $count)
          ->save();
      }
    }
    
    $description = 'Organisations (including subsidiary agencies, '
      . 'business units, departments, divisions, programmes or projects) '
      . 'that have published datasets and publications in the PDH catalogue';
    
    return [
      'count' => $count,
      'url' =>  $base_url . '/data/organization',
      'title' => 'Organizations',
      'description' => $description,
    ];
  }
  
  public function get_home_quotes(){
    $quotes = [];
    
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'quotes')
      ->pager(1);
    $nids = $query->execute();

    foreach ($nids as $nid) {
      $quote = [];
      $node = \Drupal\node\Entity\Node::load($nid); 
      $quote['name'] = $node->field_author_name->value;
      $quote['about'] = $node->field_about_the_author->value;
 
      $style = ImageStyle::load('256x256');
      $styled_image_url = $style->buildUrl($node->field_image->entity->getFileUri());
      $quote['img'] = $styled_image_url;
      
      $quote['body'] = strip_tags($node->body->value);
      
      $quotes[] = $quote;
    }

    return $quotes;
  }

}
