<?php

namespace Drupal\spc_main\Controller;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Controller\ControllerBase;

class SpcHomeController  extends ControllerBase {
  
  const DATA_BASE_PROTOCOL = 'https';
  
  const DATA_BASE_DOMAIN = 'pacificdata.org';

  public function mainLanding() {

    $data['stats'] = $this->get_spc_stats();
    $data['datasets'] = $this->get_spc_datasets();
    
    return [
        '#theme' => 'spc_home_page',
        '#data' => $data,
    ];
  }
  
  public function get_spc_stats() {
    
    $stats['datasets'] = $this->_ckan_dataset_count();
    $stats['publications'] = $this->_ckan_publications_count();
    $stats['organizations'] = $this->_ckan_organisations_count(); 
    
    return $stats;
  }
  
  public function get_spc_datasets() {
    $datasets = [];
    
    $datasets['latest'] = $this->_ckan_datasets('latest');
    $datasets['popular'] = $this->_ckan_datasets('popular');
    $datasets['url'] = self::DATA_BASE_PROTOCOL . '://' . self::DATA_BASE_DOMAIN . '/data';
    
    return $datasets;
  }
  
  public function _ckan_datasets($sort) {
    $base_url = self::DATA_BASE_PROTOCOL . '://' . self::DATA_BASE_DOMAIN;
    $data_url = $base_url . '/data/api/action/package_search';
    
    $params = '?';
    $params .= '&rows=6'; 
    
    switch ($sort){
      case 'latest':
        $params .= '&sort=' . urlencode('metadata_modified desc');    
        break;
      case 'popular':
        $params .= '&sort=' . urlencode('extras_ga_view_count desc');    
        break;
    }

    $cached_dataset = ''; //TO DO Get from cache.
    if ($cached_dataset) {
      $publications = $cached_dataset;
    } else {    
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $data_url . $params);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $result = curl_exec($ch);
      curl_close($ch);

      $responce = json_decode($result);

      if ($responce->success){
          $publications = $responce->result->results;
          //TO DO Set cache.
      }
    }

    foreach($publications as $key => $publication){
      $dataset = [];
      $dataset['name'] = $publication->name;
      $dataset['title'] = $publication->title;
      $dataset['url'] = $base_url . '/data/dataset/' . $publication->id;

      //creation date.
      $originalDate = $publication->metadata_created;
      $dataset['date'] = date("M, d, Y", strtotime($originalDate));

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
    
    $res_formats = [
        'CSV','XML', 'XLS', 'XLSX', 'ODS', 'MDB', 'MDE', 'DBF',
        'SQL', 'SQLITE', 'DB', 'DBF', 'DBS', 'ODB', 'JSON', 
        'GEOJSON', 'KML', 'KMZ', 'SHP', 'SHX', 'WMS', 'WFS', 'WCS', 'CSW'];
    $attr = ['dataset', 'service'];
    $type = ['dataset', 'biodiversity_data'];

    $base_url = self::DATA_BASE_PROTOCOL . '://' . self::DATA_BASE_DOMAIN;
    $data_url = $base_url . '/data/api/action/package_search';

    $query = '?q=';
    $params = urlencode('res_format:('. implode(' OR ', $res_formats) .')');
    $params .= urlencode(' OR dcat_type:('. implode(' OR ', $attr) .')');
    $params .= urlencode(' OR type:('. implode(' OR ', $type) .')'); 
    
    $cached_dataset_count = ''; //TO DO Get from cache.
    if ($cached_dataset_count) {
      $count = $cached_dataset_count->data;
    } else {    
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $data_url . $query. $params);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $result = curl_exec($ch);
      curl_close($ch);

      $count = 0;
      $responce = json_decode($result, true);
      if ($responce['success']){
          $count = $responce['result']['count'];
          //TO DO Set cache.
      }
    }

    $description = 'Structured data files and links to data services';
    
    $solorq = '&ext_advanced_type=solr&ext_advanced_operator=or';
    
    return [
      'count' => $count,
      'url' =>  $base_url . '/data/dataset?ext_advanced_value=' . $params . $solorq,
      'title' => 'Datasets',
      'description' => $description,
    ];
  }  
  
  public function _ckan_publications_count() {
    $res_formats = ['PDF','DOC', 'DOCX', 'ODF', 'ODT', 'EPUB', 'MOBI'];
    $attr = ['text'];
    $type = ['publications'];

    $base_url = self::DATA_BASE_PROTOCOL . '://' . self::DATA_BASE_DOMAIN;
    $data_url = $base_url . '/data/api/action/package_search';

    $query = '?q=';
    $params = urlencode('res_format:('. implode(' OR ', $res_formats) .')');
    $params .= urlencode(' OR dcat_type:('. implode(' OR ', $attr) .')');
    $params .= urlencode(' OR type:('. implode(' OR ', $type) .')');

    $cached_publications_count = ''; //TO DO Get from cache.
    if ($cached_publications_count && $topic == '') {
      $count = $cached_publications_count->data;
    } else {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $data_url . $query . $params);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $result = curl_exec($ch);
      curl_close($ch);

      $count = 0;
      $responce = json_decode($result, true);
      if ($responce['success']){
          $count = $responce['result']['count'];
          //TO DO Set cache.        
      } 
    }
    
    $description = 'Scientific papers, publications, reports, policy briefs, policies documents, manuals, handbooks';

    $solorq = '&ext_advanced_type=solr&ext_advanced_operator=or';
    
    return [
      'count' => $count,
      'url' =>  $base_url . '/data/dataset?ext_advanced_value=' . $params . $solorq,
      'title' => 'Publications',
      'description' => $description,
    ];
  }
  
  public function _ckan_organisations_count() {
    $base_url = self::DATA_BASE_PROTOCOL . '://' . self::DATA_BASE_DOMAIN;
    $organizations_url = $base_url . '/data/api/action/organization_list';
    $stats['data_url'] = $base_url . '/data/dataset';
    
    $cached_organization_count = ''; //TO DO Get from cache.
    if ($cached_organization_count){
      $count = $cached_organization_count->data;
    } else {
      $responce = json_decode(file_get_contents($organizations_url), true);
      if ($responce['success']){
          $count = count($responce['result']);
          //TO DO Set cache.
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

}
