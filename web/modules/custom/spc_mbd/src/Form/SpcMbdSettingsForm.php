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
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('landing description'),
      '#default_value' => $config->get('mbd_landing_description'),
      '#required' => false
    ]; 
    
    $form['field_mbd_zones'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Pathway to maritime zones PDF'),
      '#upload_location' => 'public://mbd/',
      '#upload_validators' => [
        'file_validate_extensions' => ['pdf'],
      ],
      '#default_value' => [$config->get('mbd_zones_fid')], 
      '#required' => false
    ];
    
    $form['field_mbd_boundary_treaty'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Pathway to Maritime Boundary Treaty PDF'),
      '#upload_location' => 'public://mbd/',
      '#upload_validators' => [
        'file_validate_extensions' => ['pdf'],
      ],
      '#default_value' => [$config->get('mbd_boundary_treaty_fid')], 
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
    
    $mbd_landing_description = $form_state->getValue('field_mbd_landing_description')['value'];
    $config->set('mbd_landing_description', $mbd_landing_description);
    
    $mbd_zones_file = $form_state->getValue('field_mbd_zones', 0);
    if (isset($mbd_zones_file[0]) && !empty($mbd_zones_file[0])) {
      $zones_file = File::load($mbd_zones_file[0]);
      $zones_file->setPermanent();
      $zones_file->save();

      $config->set('mbd_zones_fid', $zones_file->id());
    } else {
        $config->set('mbd_zones_fid', '');
    }   
    
    $mbd_boundary_treaty_file = $form_state->getValue('field_mbd_boundary_treaty', 0);
    if (isset($mbd_boundary_treaty_file[0]) && !empty($mbd_boundary_treaty_file[0])) {
      $boundary_treaty_file = File::load($mbd_boundary_treaty_file[0]);
      $boundary_treaty_file->setPermanent();
      $boundary_treaty_file->save();

      $config->set('mbd_boundary_treaty_fid', $boundary_treaty_file->id());
    } else {
        $config->set('mbd_boundary_treaty_fid', '');
    }
    
    $config->save(); 
  }
  
}
