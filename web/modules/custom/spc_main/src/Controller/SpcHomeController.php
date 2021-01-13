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

    $data['stats'] = $this->_get_spc_stats();
    
    return [
        '#theme' => 'spc_home_page',
        '#data' => $data,
    ];
  }
  
  public function _get_spc_stats() {
    
    $stats['datasets'] = $this->_ckan_dataset_count();
    $stats['publications'] = $this->_ckan_publications_count();
    $stats['organizations'] = $this->_ckan_organisations_count();  
    
    return $stats;
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
