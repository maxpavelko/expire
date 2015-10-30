<?php

/**
 * @file
 * Contains Drupal\expire\Form\ExpireSettingsForm.
 */

namespace Drupal\expire\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExpireSettingsForm.
 *
 * @package Drupal\expire\Form
 */
class ExpireSettingsForm extends ConfigFormBase {

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct($expire_component_manager) {
    $this->expireComponentManager = $expire_component_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('expire.component_manager')
    );
  }

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

    $form['tabs'] = array(
      '#type' => 'vertical_tabs',
      '#default_tab' => 'status',

    );

    // Common settings.
    $form['tabs']['status'] = array(
      '#type' => 'fieldset',
      '#title' => t('Module status'),
      '#group' => 'tabs',
      '#weight' => 0,
    );

    $components = $this->expireComponentManager->getDefinitions();

    foreach ($components as $component) {
      $instance = $this->expireComponentManager->createInstance($component['id']);
      $form['tabs'][$component['id']] = array(
        '#type' => 'fieldset',
        '#title' => $component['label'],
        '#group' => 'tabs',
        '#weight' => 0,
      ) + $instance->settingsForm($form, $form_state);
    }

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
