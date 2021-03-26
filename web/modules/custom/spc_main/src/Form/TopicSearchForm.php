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

class TopicSearchForm extends FormBase {

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
    $data_base_url = \Drupal::config('spc_publication_import.settings')->get('spc_base_url');
    
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface && $node->getType() == 'thematic_group') {
      $topic = $node->getTitle();
      $term = '?extras_thematic_area_string=' . $topic;
    }
    
    $tag_query = '&q=';
    if ($form_state->getValue('term')){
      $tag_query .= $form_state->getValue('term');
    }


    $redirect_url = rtrim($data_base_url) . 'dataset' . $term . $tag_query;

    $response = new TrustedRedirectResponse(Url::fromUri($redirect_url)->toString());
    $form_state->setResponse($response);
  }
  
}
