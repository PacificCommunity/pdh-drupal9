<?php

namespace Drupal\spc_mbd\Form;

use Drupal\Core\Form\ConfigFormBase; 
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Description of SpcMbdSettingsForm
 *
 * @author sershch
 */
class SpcMbdSettingsForm extends ConfigFormBase {
    
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {
    return [  
      'spc_mbd.settings',
    ];  
  }
  
  /**  
   * {@inheritdoc}  
   */   
  public function getFormId(){
    return 'spc_mbd_settings_form';
  }
  
    /**  
   * {@inheritdoc}  
   */   
  public function buildForm(array $form, FormStateInterface $form_state){

    $config = \Drupal::config('spc_mbd.settings');
    
    $form['field_mbd_landing_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('landing title'),
      '#default_value' => $config->get('mbd_landing_title'),
      '#required' => true
    ];
    
    $form['field_mbd_landing_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('landing title'),
      '#default_value' => $config->get('mbd_landing_description'),
      '#required' => false
    ];    

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    $config = \Drupal::configFactory()->getEditable('spc_mbd.settings');
    
    $mbd_landing_title = $form_state->getValue('field_mbd_landing_title');
    $config->set('mbd_landing_title', $mbd_landing_title);
    
    $mbd_landing_description = $form_state->getValue('field_mbd_landing_description');
    $config->set('mbd_landing_description', $mbd_landing_description);
    
    $config->save(); 
  }
  
}
