<?php

/**
 * @file
 * Contains \Drupal\spc_main\Plugin\Block\spcMainBanner.
 */
namespace Drupal\spc_main\Plugin\Block;

use Drupal\Core\Block\BlockBase;    
use Drupal\Core\Link;
use Drupal\Core\Url;
/**
 * Provides a sps_topic_banner.
 *
 * @Block(
 *   id = "sps_topic_banner",
 *   admin_label = @Translation("SPC Topic Banner"),
 *   category = @Translation("SPC block")
 * )
 */
class spcTopicBanner extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $data_base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
      
    $data = [];
    $data['title'] = '';
    $data['subtitle'] = '';
    $data['topics'] = $this->get_topics();

    $data['search_form'] = \Drupal::formBuilder()->getForm('Drupal\spc_main\Form\TopicSearchForm');

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface && $node->getType() == 'thematic_group') {
      $title = $node->getTitle();
      $data['current_topic'] = [
          'title' =>  $title,
          'icon' => $node->field_fa_icon->value,
      ];
      
      $data['stats'] = [
          'datasets' => [
              'count' => $node->field_stats_datasets_count->value,
              'url' => $data_base_url . 'dataset?ext_advanced_value=res_format%3A%28CSV+OR+XML+OR+XLS+OR+XLSX+OR+ODS+OR+MDB+OR+MDE+OR+DBF+OR+SQL+OR+SQLITE+OR+DB+OR+DBF+OR+DBS+OR+ODB+OR+JSON+OR+GEOJSON+OR+KML+OR+KMZ+OR+SHP+OR+SHX+OR+WMS+OR+WFS+OR+WCS+OR+CSW%29+OR+dcat_type%3A%28dataset+OR+service%29+OR+type%3A%28dataset+OR+biodiversity_data%29+AND+extras_thematic_area_string:'.$title.'&ext_advanced_type=solr&ext_advanced_operator=or',
          ],
          'publications' => [
              'count' => $node->field_stats_publications_count->value,
              'url' => $data_base_url . 'dataset?ext_advanced_value=res_format%3A%28PDF+OR+DOC+OR+DOCX+OR+ODF+OR+ODT+OR+EPUB+OR+MOBI%29+OR+dcat_type%3A%28text%29+OR+type%3A%28publications%29+AND+extras_thematic_area_string:' . $title . '&ext_advanced_type=solr&ext_advanced_operator=or',
          ],
          'organisations' => [
              'count' => $node->field_stats_organisations_count->value,
              'url' => $data_base_url . 'organization'
          ],
      ];
    }

    return array(
      '#theme' => 'topic_banner_block',
      '#cache' => ['max-age'=> 0],
      '#data' => $data,
    );
  }
  
  public function get_topics(){
    $topics = [];
    
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)   
      ->condition('type', 'thematic_group')
      ->pager(10);
    $nids = $query->execute();

    foreach ($nids as $nid) {
      $topic = [];
      $node = \Drupal\node\Entity\Node::load($nid);
      
      $topic['url'] = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid);
      $topic['title'] = $node->title->value;
      $topic['icon'] = $node->field_fa_icon->value;

      $topics[] = $topic;        
    }  

    return $topics;
  }
  
}
