<?php

namespace Drupal\spc_publication_import\Form;

use Drupal\Core\Form\ConfigFormBase; 
use Drupal\Core\Form\FormStateInterface;

class SpcPublicationImportSettingsForm extends ConfigFormBase {
  
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {
    return [  
      'spc_publication_import.settings',
    ];  
  }
  
  /**  
   * {@inheritdoc}  
   */   
  public function getFormId(){
    return 'spc_publications_import_settings_form';
  }
  
  /**  
   * {@inheritdoc}  
   */   
  public function buildForm(array $form, FormStateInterface $form_state){

    $config = \Drupal::config('spc_publication_import.settings');

    $form['field_spc_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SPC base url'),
      '#required' => TRUE,
      '#default_value' => $config->get('spc_base_url') ?? false,
    ];
    
    $form['home_stats'] = array(
      '#type' => 'details',
      '#title' => t('Home statistics'),
      '#open' => false, 
    );
    
    $form['home_stats']['field_spc_datasets_count'] = [
      '#type' => 'textfield',     
      '#title' => $this->t('Datasets count'),
      '#required' => false,
      '#default_value' => $config->get('spc_datasets_count') ?? false,
    ];   

    $form['home_stats']['field_spc_publications_count'] = [
      '#type' => 'textfield',     
      '#title' => $this->t('Publications count'),
      '#required' => false,
      '#default_value' => $config->get('spc_publications_count') ?? false,
    ];

    $form['home_stats']['field_spc_organisations_count'] = [
      '#type' => 'textfield',     
      '#title' => $this->t('Organisations count'),
      '#required' => false,
      '#default_value' => $config->get('spc_organisations_count') ?? false,
    ];    
    
    $form['home_publications'] = array(
      '#type' => 'details',
      '#title' => t('Home publications'),
      '#open' => false, 
    );    
    
    $form['home_publications']['field_spc_publications_latest'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['plain_text'],      
      '#title' => $this->t('SPC publications latest'),
      '#required' => false,
      '#default_value' => $config->get('spc_publications_latest') ?? false,
    ];
    
    $form['home_publications']['field_spc_publications_popular'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['plain_text'],      
      '#title' => $this->t('SPC publications popular'),
      '#required' => false,
      '#default_value' => $config->get('spc_publications_popular') ?? false,
    ];

    return parent::buildForm($form, $form_state); 
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    parent::submitForm($form, $form_state);
    
    \Drupal::configFactory()->getEditable('spc_publication_import.settings')
      ->set('spc_base_url', $form_state->getValue('field_spc_base_url'))   
            
      ->set('spc_datasets_count', @$form_state->getValue('field_spc_datasets_count')['value'])
      ->set('spc_publications_count', @$form_state->getValue('field_spc_publications_count')['value'])
      ->set('spc_organisations_count', @$form_state->getValue('field_spc_organisations_count')['value'])            
            
      ->set('spc_publications_latest', @$form_state->getValue('field_spc_publications_latest')['value'])
      ->set('spc_publications_popular', @$form_state->getValue('field_spc_publications_popular')['value'])            
      ->save();
  }
}
