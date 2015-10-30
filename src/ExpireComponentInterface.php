<?php
/**
 * Created by PhpStorm.
 * User: vm386
 * Date: 10/30/15
 * Time: 1:39 PM
 */

namespace Drupal\expire;

use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Field\PluginSettingsInterface;



interface ExpireComponentInterface extends PluginSettingsInterface {
  /**
   * Returns a form to configure settings for the expire component.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form elements for the formatter settings.
   */
  public function settingsForm(array $form, FormStateInterface $form_state);

  /**
   * Executes expiration actions for node.
   *
   * @param object
   *   Component object.
   *
   * @param $action
   *   Action that has been executed.
   *
   * @param $skip_action_check
   *   Shows whether should we check executed action or just expire node.
   */
  public function expire($object, $action, $skip_action_check = FALSE);
}