<?php

namespace Drupal\expire\Plugin\ExpireComponent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\expire\ExpireComponentBase;

/**
 * Plugin implementation of the 'node' component.
 *
 *
 * @ExpireComponent(
 *   id = "node",
 *   label = @Translation("Node")
 * )
 */
class NodeExpireComponent extends ExpireComponentBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);


    return $elements;
  }

}