<?php

namespace Drupal\spc_main\Form;

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
    
    $form['field_spc_publications'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['plain_text'],      
      '#title' => $this->t('SPC publications json'),
      '#description' => $this->t('SPC publications json.'),
      '#required' => false,
      '#default_value' => $config->get('spc_publications') ?? false,
    ];
    
    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Previous revision'),
      '#open' => false, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );
    
    $form['advanced']['field_spc_publications_revision'] = [
      '#type' => 'text_format',
      '#allowed_formats' => ['plain_text'],      
      '#title' => $this->t('SPC publications json revision'),
      '#description' => $this->t('SPC publications json revision.'),
      '#required' => false,
      '#disabled' => TRUE,
      '#default_value' => $config->get('spc_publications_revision') ?? false,
      '#open' => TRUE,
    ];   

    $form['field_spc_publications_revision_apply'] = [
      '#type' => 'checkbox',     
      '#title' => $this->t('Apply previous revision'),
      '#description' => $this->t('SPC publications json revision apply.'),
      '#required' => false,
      '#default_value' => $config->get('spc_publications_revision_apply') ?? false,
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
      ->set('spc_publications', $form_state->getValue('field_spc_publications')['value'])
      ->set('spc_publications_revision_apply', $form_state->getValue('field_spc_publications_revision_apply'))
      ->save();
  }
}
