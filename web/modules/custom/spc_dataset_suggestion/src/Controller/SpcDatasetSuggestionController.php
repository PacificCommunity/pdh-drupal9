<?php
/**
 * @file
 * Contains \Drupal\spc_dataset_suggestion\Controller\SpcDatasetSuggestionController.
 */
namespace Drupal\spc_dataset_suggestion\Controller;

class SpcDatasetSuggestionController {
  public function content() {
    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create(['type' => 'dataset_suggestion']);

    $node->title->setValue('hey');
    $node->body->setValue('hey');
    $node->field_suggester_name->setValue('hey');
    $node->field_suggester_email->setValue('hey@he.lh');

    $form_arg = \Drupal::entityTypeManager()
      ->getFormObject('node', 'default')
      ->setEntity($node);

    $form = \Drupal::formBuilder()->getForm($form_arg);

    return array(
      '#theme' => 'dataset_suggestion_form',
      '#form' => $form,
    );
  }
}
