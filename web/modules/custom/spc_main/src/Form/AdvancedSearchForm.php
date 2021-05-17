<?php

/**
 * @file
 * Contains Drupal\spc_main\Form.
 */

namespace Drupal\spc_main\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;

class AdvancedSearchForm extends FormBase {
  
  const DATA_BASE_URL = 'https://pacificdata.org';

  const FACETS_CATEGORIES = '"tags","organization","res_format","license_id","topic","type","member_countries"';

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

    $config = \Drupal::configFactory()->getEditable('spc_main.settings');    
    $search_facets = $config->get('search_facets');
    
    if (!$search_facets){
      $url = self::DATA_BASE_URL . '/data/api/action/package_search?facet.field=[' . self::FACETS_CATEGORIES . ']';
      $responce = json_decode(file_get_contents($url), true);

      if ($responce['success']){
        $search_facets = $responce['result']['search_facets'];
        $config->set('search_facets', $search_facets);
        $config->save();
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
                  . $this->t('Advanced search')
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
    
    $term = $tags = $organization = $res_format = $license_id = $topic = $type = $member_countries = NULL;

    $term = $form_state->getValue('term');
    $tags = $form_state->getValue('tags');
    $topic = $form_state->getValue('topic');
    $type = $form_state->getValue('type');    
    $organization = $form_state->getValue('organization');
    $res_format = $form_state->getValue('res_format');
    $license_id = $form_state->getValue('license_id');

    $member_countries = $form_state->getValue('member_countries');
    
    $tag_query = '';
    if ($tags && $tags != 'none') {
      $tag_query .= '&tags=' . implode('&tags=', $tags);
    }
    if ($organization && $organization != 'none') {
      $tag_query .= '&organization=' . implode('&organization=', $organization);
    }
    if ($res_format && $res_format != 'none') {
      $tag_query .= '&res_format=' . implode('&res_format=', $res_format);
    }
    if ($license_id && $license_id != 'none') {
      $tag_query .= '&license_id=' . implode('&license_id=', $license_id);
    }
    if ($topic && $topic != 'none') {
      $tag_query .= '&topic=' . implode('&topic=', $topic);
    }
    if ($type && $type != 'none') {
      $tag_query .= '&type=' . implode('&type=', $type);
    }
    if ($member_countries && $member_countries != 'none') {
      $tag_query .= '&member_countries=' . implode('&member_countries=', $member_countries);
    }

    $redirect_url = rtrim( self::DATA_BASE_URL) . '/data/dataset?q=' . $term . $tag_query;

    $response = new TrustedRedirectResponse(Url::fromUri($redirect_url)->toString());
    $form_state->setResponse($response);
  }
  
}
