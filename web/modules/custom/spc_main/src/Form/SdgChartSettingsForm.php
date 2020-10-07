<?php

namespace Drupal\spc_main\Form;

use Drupal\Core\Form\ConfigFormBase; 
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Description of SdgChartSettings
 *
 * @author sershch
 */
class SdgChartSettingsForm extends ConfigFormBase {
    
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {
    return [  
      'spc_main.settings',
    ];  
  }
  
  /**  
   * {@inheritdoc}  
   */   
  public function getFormId(){
    return 'sdg_chart_settings_form';
  }
  
    /**  
   * {@inheritdoc}  
   */   
  public function buildForm(array $form, FormStateInterface $form_state){

    $config = \Drupal::config('spc_main.settings');
    
    $form['field_sdg_countries'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Countries json'),
      '#upload_location' => 'public://chart/sdg',
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#default_value' => [$config->get('sdg_countries_fid')], 
      '#required' => true
    ];
    
    $form['field_sdg_domains'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Domains json'),
      '#upload_location' => 'public://chart/sdg',
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#default_value' => [$config->get('sdg_domains_fid')],
      '#required' => true
    ];
    
    $form['field_sdg_goals'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Goals json'),
      '#upload_location' => 'public://chart/sdg',
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#default_value' => [$config->get('sdg_goals_fid')],
      '#required' => true
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    $config = \Drupal::configFactory()->getEditable('spc_main.settings');
    
    $form_countries_file = $form_state->getValue('field_sdg_countries', 0);
    if (isset($form_countries_file[0]) && !empty($form_countries_file[0])) {
      $countries_file = File::load($form_countries_file[0]);
      $countries_file->setPermanent();
      $countries_file->save();

      $config->set('sdg_countries_fid', $countries_file->id());
    }  
    
    $form_domains_file = $form_state->getValue('field_sdg_domains', 0);
    if (isset($form_domains_file[0]) && !empty($form_domains_file[0])) {
      $domains_file = File::load($form_domains_file[0]);
      $domains_file->setPermanent();
      $domains_file->save();

      $config->set('sdg_domains_fid', $domains_file->id());
    }    
    
    $form_goals_file = $form_state->getValue('field_sdg_goals', 0);
    if (isset($form_goals_file[0]) && !empty($form_goals_file[0])) {
      $goals_file = File::load($form_goals_file[0]);
      $goals_file->setPermanent();
      $goals_file->save();

      $config->set('sdg_goals_fid', $goals_file->id());
    }
    
    $config->save(); 
  }
  
}
