<?php

namespace Drupal\spc_main\Form;

use Drupal\Core\Form\ConfigFormBase; 
use Drupal\Core\Form\FormStateInterface;

class SpcImportSettingsForm extends ConfigFormBase {
  
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {
    return [  
      'spc_data_import.settings',
    ];  
  }
  
  /**  
   * {@inheritdoc}  
   */   
  public function getFormId(){
    return 'spc_import_settings_form';
  }
  
  /**  
   * {@inheritdoc}  
   */   
  public function buildForm(array $form, FormStateInterface $form_state){

    $config = \Drupal::config('spc_data_import.settings');

    $form['field_spc_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SPC base url'),
      '#required' => TRUE,
      '#default_value' => $config->get('spc_base_url') ?? false,
    ];
    
    $form['field_spc_header_markup'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['full_html', 'plain_text'],      
      '#title' => $this->t('SPC header markup'),
      '#description' => $this->t('SPC header markup.'),
      '#required' => false,
      '#default_value' => $config->get('header_markup') ?? false,
    ];
    
    $form['field_spc_footer_markup'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['full_html', 'plain_text'],      
      '#title' => $this->t('SPC footer markup'),
      '#description' => $this->t('SPC footer markup.'),
      '#required' => false,
      '#default_value' => $config->get('footer_markup') ?? false,
    ];
    
    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Previous revision'),
      '#open' => false, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    ); 
    
    $form['advanced']['field_spc_header_markup_rvision'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['full_html', 'plain_text'],      
      '#title' => $this->t('SPC header markup revision'),
      '#description' => $this->t('SPC header markup revision.'),
      '#required' => false,
      '#disabled' => TRUE,
      '#default_value' => $config->get('header_markup_revision') ?? false,
    ];
    
    $form['advanced']['field_spc_footer_markup_revision'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['full_html', 'plain_text'],      
      '#title' => $this->t('SPC footer markup revision'),
      '#description' => $this->t('SPC footer markup revision.'),
      '#required' => false,
      '#disabled' => TRUE,
      '#default_value' => $config->get('footer_markup_revision') ?? false,
    ];
    
    $form['field_spc_html_revision_apply'] = [
      '#type' => 'checkbox',     
      '#title' => $this->t('Apply previous revision'),
      '#description' => $this->t('Apply HTML revision.'),
      '#required' => false,
      '#default_value' => $config->get('revision_apply') ?? false,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    parent::submitForm($form, $form_state);
    
    \Drupal::configFactory()->getEditable('spc_data_import.settings')
      ->set('spc_base_url', $form_state->getValue('field_spc_base_url'))   
      ->set('header_markup', $form_state->getValue('field_spc_header_markup')['value'])
      ->set('footer_markup', $form_state->getValue('field_spc_footer_markup')['value'])
      ->set('revision_apply', $form_state->getValue('field_spc_html_revision_apply'))
      ->save(); 
  }
}
