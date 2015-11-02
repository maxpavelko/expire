<?php
/**
 * Created by PhpStorm.
 * User: vm386
 * Date: 10/30/15
 * Time: 2:06 PM
 */

namespace Drupal\expire;

use Drupal\Core\Field\PluginSettingsBase;
use Drupal\Core\Form\FormStateInterface;

class ExpireComponentBase extends PluginSettingsBase implements ExpireComponentInterface {

  /**
   * Constructs a FormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   */
  public function __construct($plugin_id, $plugin_definition, array $settings) {
    parent::__construct(array(), $plugin_id, $plugin_definition);

    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function expire($object, $action, $skip_action_check = FALSE) {}
}