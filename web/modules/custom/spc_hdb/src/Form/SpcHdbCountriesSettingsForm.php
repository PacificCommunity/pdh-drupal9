<?php

namespace Drupal\spc_hdb\Form;

use Drupal\Core\Form\ConfigFormBase; 
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Term;

/**
 * Description of SpcHdbSettingsForm
 *
 * @author sershch
 */
class SpcHdbCountriesSettingsForm extends ConfigFormBase {
    
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
    return 'spc_hdb_countries_settings_form';
  }
  
    /**  
   * {@inheritdoc}  
   */   
  public function buildForm(array $form, FormStateInterface $form_state){
    $config = \Drupal::config('spc_hdb.settings');
    $fid = $config->get('health_json_fid');
    $file = File::load($fid);
    
    if (is_object($file)){
      $furl = file_create_url($file->getFileUri());
      $fcontent = file_get_contents($furl);
      $health_dashboard_data = json_decode($fcontent, true);
      
      foreach($health_dashboard_data['countries-data'] as $country_data){
        $name = $country_data['title'];
        $country_code = $country_data['id'];

        $form['pdf_' . $country_code . '_file'] = [
          '#type' => 'managed_file',
          '#title' => t('Upload '. $name .' health dashboard PDF File'),
          '#description' => t('File will be able to download on <a href="/dashboard/health-dashboard/country/' . $country_code . '">' . $name . '</a> country page.'),
          '#upload_validators' => [
            'file_validate_extensions' => ['pdf'],
          ],
          '#upload_location' => 'public://pdf/',
          '#default_value' => [$config->get('pdf_' . $country_code . '_fid')],
          '#required' => FALSE,    
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    $config = \Drupal::getContainer()->get('config.factory')->getEditable('spc_hdb.settings');
    $fid = $config->get('health_json_fid');
    $file = File::load($fid);
    
    if (is_object($file)){
      $furl = file_create_url($file->getFileUri());
      $fcontent = file_get_contents($furl);
      $health_dashboard_data = json_decode($fcontent, true);
      
      foreach($health_dashboard_data['countries-data'] as $country_data){
        $name = $country_data['title'];
        $country_code = $country_data['id'];

        $file_value = $form_state->getValue('pdf_' . $country_code . '_file', 0);
        if (isset($file_value[0]) && !empty($file_value[0])) {
          $file = File::load($file_value[0]);
          $file->setPermanent();
          $file->save();
          $config->set('pdf_' . $country_code . '_fid', $file->id());
        } else {
            $config->set('pdf_' . $country_code . '_fid', '');
        }
      }
    }

    $config->save(); 
  }
  
}
