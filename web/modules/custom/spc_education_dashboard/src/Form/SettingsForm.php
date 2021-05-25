<?php

namespace Drupal\spc_education_dashboard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\file\Entity\File;

/**
 * Configure SPC Education Dashboard settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spc_education_dashboard_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spc_education_dashboard.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('spc_education_dashboard.settings');
    $default_fid = $config->get('education_json_fid') ?? 0;
    $form['education_json_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload a file'),
      '#description' => $this->t('Upload the Education Dashboard JSON File her.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#upload_location' => 'public://hdb/',
      '#default_value' => [$default_fid],
      '#required' => true,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if ($form_state->getValue('example') != 'example') {
      // $form_state->setErrorByName('example', $this->t('The value is not correct.'));
    // }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('spc_education_dashboard.settings');

    $health_json_file = $form_state->getValue('education_json_file', 0);
    // if (isset($health_json_file[0]) && !empty($health_json_file[0])) {
      $file = File::load($health_json_file[0]);
      $file->setPermanent();
      $file->save();
      $config->set('education_json_fid', $file->id());
    // } else {
        // $config->set('education_json_fid', '');
    // }

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
