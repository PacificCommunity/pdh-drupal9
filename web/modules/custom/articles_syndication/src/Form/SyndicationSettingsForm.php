<?php

/**
 * @file
 * Contains Drupal\articles_syndication\Form.
 */

namespace Drupal\articles_syndication\Form;

use Drupal\Core\Form\ConfigFormBase; 
use Drupal\Core\Form\FormStateInterface;

class SyndicationSettingsForm extends ConfigFormBase {
  
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {
    return [  
      'articles_syndication.settings',
    ];  
  }  
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'syndication_settings_form';
  }  
  
  /**
   * {@inheritdoc}
   * Form
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $config = \Drupal::config('articles_syndication.settings');
    
    # Text field
    $form['field_syndication_url'] = [
      '#type' => 'textfield',
      '#title' => t('Syndicated base url'),
      '#default_value' => $config->get('syndication_url') ?? null,
      '#required' => false
    ];
    
    # Text field
    $form['field_syndication_email'] = [
      '#type' => 'textfield',
      '#title' => t('Syndicated email'),
      '#default_value' => $config->get('syndication_email') ?? 'datahub@spc.int',
      '#required' => false
    ];
    
    # Text field
    $form['field_syndicated_synpass'] = [
      '#type' => 'textfield',
      '#title' => t('Syndicated hash'),
      '#default_value' => $config->get('syndicated_synpass') ?? null,
      '#required' => false
    ];    

    return parent::buildForm($form, $form_state);
  }
  
  /**
   * {@inheritdoc}
   * Submit
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    parent::submitForm($form, $form_state);
    
    \Drupal::configFactory()->getEditable('articles_syndication.settings')
      ->set('syndication_url', $form_state->getValue('field_syndication_url'))
      ->set('syndication_email', $form_state->getValue('field_syndication_email'))
      ->set('syndicated_synpass', $form_state->getValue('field_syndicated_synpass'))      
      ->save();
    
  }
}
