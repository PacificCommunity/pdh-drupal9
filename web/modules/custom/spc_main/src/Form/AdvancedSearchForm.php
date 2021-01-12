<?php

/**
 * @file
 * Contains Drupal\spc_main\Form.
 */

namespace Drupal\spc_main\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AdvancedSearchForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckan_search_form';
  }
  /**
   * {@inheritdoc}
   * Form
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    # Text field
    $form['term'] = [
      '#type' => 'textfield',
      '#required' => false,
      '#attributes' => [
        'placeholder' => 'Search'
      ]
    ];
    
    $search_facets = [];
    if (!$search_facets){
      
      define('DATA_BASE_URL', 'https://pacificdata.org');
      define('FACETS_CATEGORIES', '"tags","organization","res_format","license_id","topic","type","member_countries"');

      $url = DATA_BASE_URL . '/data/api/action/package_search?facet.field=[' . FACETS_CATEGORIES . ']';
      $responce = json_decode(file_get_contents($url), true);

      if ($responce['success']){
        $search_facets = $responce['result']['search_facets'];
      }    
    }
    
    $form['advanced'] = [
      '#type' => 'fieldset',
      '#title' => t('Advanced search'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#open' => TRUE,  
    ];
    
    $numItems = count($search_facets);
    $i = 0;
    foreach($search_facets as $name => $list){
      $i++;
      $options = [];
      if ($name == 'member_countries') {            
        foreach($list['items'] as $item){
            @$options[$item['name']] = $mapping_config['mapping']['member_countries'][$item['name']];
        }
      } else {
        foreach($list['items'] as $item){
            $options[$item['name']] = str_replace(['_', '--', '-'], ' ', $item['display_name']);
        }
      }

      $prefix = '<div class="filter-wrapp ' . $name . '">';
      if ($i === 1){
        $prefix = '<div class="filter-group"><div class="filter-wrapp ' . $name . '">';
      } elseif ($i === 5) {
        $prefix = '</div><div class="filter-group"><div class="filter-wrapp ' . $name . '">';
      }

      $suffix = '</div>';
      if($i === $numItems) {
        $suffix .= ''
        . '<div class="filter-wrapp">'
            . '<a id="adv-search-submit" href="#"><span>'.t('Search').'</span></a>'
        . '</div></div>'
        . '<div class="inner">'
          . '<span class="fieldset-legend">'
              . '<a class="fieldset-title" href="#">'
                  . 'Advanced search'
              . '</a>'
          . '</span>'
        . '</div>';
      }

      if ($i === $numItems/2){
        $suffix .= '</div>';
      }

      $title = str_replace('_', ' ', $name);
      if ($title == 'res format'){
        $title = 'format';
      }

      $form['advanced'][$name] = array(
        '#prefix' => $prefix,
        '#title' => $title,
        '#type' => 'select',
        '#multiple' => true,  
        '#required' => false,
        '#attributes' => array(
          'placeholder' => 'Search',
          'data-name' => $name,
          'data-title' => $title,
          'class'=> ['filter']  
        ),
        '#options' => $options,
        '#suffix' => $suffix,
        '#disabled' => false,
        '#default_value' => array(),
        '#validated' => TRUE,
      );
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => '',        
    ];

    return $form;
  }
  /**
   * {@inheritdoc}
   * Submit
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

//    $term = $search_type = $thematic_area = $ckan_tags = NULL;
//    $tags = $organization = $res_format = $license_id = $topic = $type = $member_countries = NULL;
//    extract($form_state['values'], EXTR_IF_EXISTS);
//    switch ($search_type) {
//      case 'article':
//        $redirect_info = [sprintf('/search/articles/%s/%s', $term, $thematic_area)];
//        break;
//
//      case 'dataset':
//      default:
//        $tag_query = '';
//        if ($tags && $tags != 'none') {
//          $tag_query .= '&tags=' . implode('&tags=', $tags);
//        }
//        if ($organization && $organization != 'none') {
//          $tag_query .= '&organization=' . implode('&organization=', $organization);
//        }
//        if ($res_format && $res_format != 'none') {
//          $tag_query .= '&res_format=' . implode('&res_format=', $res_format);
//        }
//        if ($license_id && $license_id != 'none') {
//          $tag_query .= '&license_id=' . implode('&license_id=', $license_id);
//        }
//        if ($topic && $topic != 'none') {
//          $tag_query .= '&topic=' . implode('&topic=', $topic);
//        }
//        if ($type && $type != 'none') {
//          $tag_query .= '&type=' . implode('&type=', $type);
//        }
//        if ($member_countries && $member_countries != 'none') {
//          $tag_query .= '&member_countries=' . implode('&member_countries=', $member_countries);
//        }       
//        $redirect_info = [
//          rtrim(variable_get('data_base_url', DATA_BASE_URL))
//          . '/data/dataset?'
//          . drupal_http_build_query([
//            CKAN_SEARCH_NAME_THEMATIC_AREA => $thematic_area,
//            'q' => $term,
//          ]) . $tag_query
//        ];
//        break;
//    }
//
//    $form_state['redirect'] = $redirect_info;

  }
}
