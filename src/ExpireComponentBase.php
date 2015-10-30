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
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return array();
  }

  public function expire($object, $action, $skip_action_check = FALSE) {}
}