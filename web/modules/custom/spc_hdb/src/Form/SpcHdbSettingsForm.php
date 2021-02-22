<?php

namespace Drupal\spc_hdb\Form;

use Drupal\Core\Form\ConfigFormBase; 
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Description of SpcHdbSettingsForm
 *
 * @author sershch
 */
class SpcHdbSettingsForm extends ConfigFormBase {
    
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {
    return [  
      'spc_hdb.settings',
    ];  
  }
  
  /**  
   * {@inheritdoc}  
   */   
  public function getFormId(){
    return 'spc_hdb_settings_form';
  }
  
    /**  
   * {@inheritdoc}  
   */   
  public function buildForm(array $form, FormStateInterface $form_state){

    $config = \Drupal::config('spc_hdb.settings');
    
    $form['field_hdb_landing_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Landing title'),
      '#default_value' => $config->get('hdb_landing_title'),
      '#required' => true
    ];
    
    $form['field_hdb_landing_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Landing sub title'),
      '#default_value' => $config->get('hdb_landing_subtitle'),
      '#required' => true
    ];    
    
    $form['field_hdb_landing_description'] = [
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#title' => $this->t('Landing description'),
      '#default_value' => $config->get('hdb_landing_description'),
      '#required' => false
    ];     
    
    $form['health_json_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Health Countries File'),
      '#description' => t('Upload a Countries JSON File her.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#upload_location' => 'public://hdb/',
      '#default_value' => [$config->get('health_json_fid')],
      '#required' => FALSE,
    ];
    
    $form['health_categories_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Health Categories File'),
      '#description' => t('Upload a Health Categories JSON File her.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#upload_location' => 'public://hdb/',
      '#default_value' => [$config->get('health_categories_fid')],
      '#required' => true,
    ];
    
    $form['health_indicators_file'] = [
      '#type' => 'managed_file',
      '#title' => t('Upload Health Indicators File'),
      '#description' => t('Upload a Health Indicators JSON File her.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#upload_location' => 'public://hdb/',
      '#default_value' => [$config->get('health_indicators_fid')],
      '#required' => true,
    ];
    
    $form['health_chart_download_file'] = [
      '#type' => 'managed_file',
      '#title' => t('Upload Health Chart Download File'),
      '#description' => t('Upload a Health Chart PDF File her.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['pdf'],
      ],
      '#upload_location' => 'public://hdb/',
      '#default_value' => [$config->get('health_chart_download_fid')],
      '#required' => FALSE,
    ];    

    return parent::buildForm(@$form, @$form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    $config = \Drupal::configFactory()->getEditable('spc_hdb.settings');
    
    $mbd_landing_title = $form_state->getValue('field_hdb_landing_title');
    $config->set('hdb_landing_title', $mbd_landing_title);
    
    $mbd_landing_title = $form_state->getValue('field_hdb_landing_subtitle');
    $config->set('hdb_landing_subtitle', $mbd_landing_title);
    
    $mbd_landing_description = $form_state->getValue('field_hdb_landing_description')['value'];
    $config->set('hdb_landing_description', $mbd_landing_description);    
    
    $health_json_file = $form_state->getValue('health_json_file', 0);
    if (isset($health_json_file[0]) && !empty($health_json_file[0])) {
      $file = File::load($health_json_file[0]);
      $file->setPermanent();
      $file->save();
      $config->set('health_json_fid', $file->id());
    } else {
        $config->set('health_json_fid', '');
    }   
    
    $health_categories_file = $form_state->getValue('health_categories_file', 0);
    if (isset($health_categories_file[0]) && !empty($health_categories_file[0])) {
      $file = File::load($health_categories_file[0]);
      $file->setPermanent();
      $file->save();
      $config->set('health_categories_fid', $file->id());
    } else {
        $config->set('health_categories_fid', '');
    }
    
    $health_indicators_file = $form_state->getValue('health_indicators_file', 0);
    if (isset($health_indicators_file[0]) && !empty($health_indicators_file[0])) {
      $file = File::load($health_indicators_file[0]);
      $file->setPermanent();
      $file->save();
      $config->set('health_indicators_fid', $file->id());
    } else {
        $config->set('health_indicators_fid', '');
    }

    $health_chart_download_file = $form_state->getValue('health_chart_download_file', 0);
    if (isset($health_chart_download_file[0]) && !empty($health_chart_download_file[0])) {
      $file = File::load($health_chart_download_file[0]);
      $file->setPermanent();
      $file->save();
      $config->set('health_chart_download_fid', $file->id());
    } else {
        $config->set('health_chart_download_fid', '');
    }    
    
    $config->save(); 
  }
  
}
