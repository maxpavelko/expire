<?php

/**
 * @file
 * Contains Drupal\expire\Form\ExpireSettingsForm.
 */

namespace Drupal\expire\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExpireSettingsForm.
 *
 * @package Drupal\expire\Form
 */
class ExpireSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'expire.settings'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'expire_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('expire.settings');

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('expire.settings')
      ->save();
  }

}
