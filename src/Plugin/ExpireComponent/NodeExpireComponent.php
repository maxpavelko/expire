<?php

namespace Drupal\expire\Plugin\ExpireComponent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
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

    $elements['override'] = array(
      '#type' => 'fieldset',
      '#title' => t('Node settings per type'),
    );

    $elements['override']['info'] = array(
      '#type' => 'item',
      '#markup' => t('Please note that you may override this settings for each node type on the node type configuration page.'),
    );

    $elements['actions'] = array(
      '#type' => 'fieldset',
      '#title' => t('Node actions'),
    );

    $elements['actions']['expire_node_actions'] = array(
      '#type' => 'checkboxes',
      '#description' => t('Page cache for node will be flushed after selected actions.'),
      '#options' => array(
        EXPIRE_NODE_INSERT => t('Node insert'),
        EXPIRE_NODE_UPDATE => t('Node update'),
        EXPIRE_NODE_DELETE => t('Node delete'),
      ),
      '#default_value' => '',
    );

    $elements['expire'] = array(
      '#type' => 'fieldset',
      '#title' => t('What URLs should be expired when node action is triggered?'),
    );

    $elements['expire']['expire_node_front_page'] = array(
      '#type' => 'checkbox',
      '#title' => t('Front page'),
      '#description' => t('Expire url of the site front page'),
      '#default_value' => '',
    );

    $elements['expire']['expire_node_node_page'] = array(
      '#type' => 'checkbox',
      '#title' => t('Node page'),
      '#description' => t('Expire url of the expiring node.'),
      '#default_value' => '',
    );

    if (\Drupal::moduleHandler()->moduleExists('taxonomy')) {
      $elements['expire']['expire_node_term_pages'] = array(
        '#type' => 'checkbox',
        '#title' => t('Node term pages'),
        '#description' => t('Expire urls of terms that are selected in the expiring node.'),
        '#default_value' => '',
      );
    }


    $elements['expire']['expire_node_reference_pages'] = array(
      '#type' => 'checkbox',
      '#title' => t('Node reference pages'),
      '#description' => t('Expire urls of entities which are referenced from the expiring node.'),
      '#default_value' => '',
    );

    if (\Drupal::moduleHandler()->moduleExists('field_collection')) {
      $elements['expire']['expire_node_reference_field_collection_pages'] = array(
        '#type' => 'checkbox',
        '#title' => t('Traverse references attached to field collections'),
        '#description' => t('Expire urls of entities which are referenced from field collections attached to the expiring node.'),
        '#default_value' => '',
        '#states' => array(
          'visible' => array(
            ':input[name="expire_node_reference_pages"]' => array('checked' => TRUE),
          ),
        ),
      );
    }

    $elements['expire']['expire_node_custom'] = array(
      '#type' => 'checkbox',
      '#title' => t('Custom pages'),
      '#description' => t('Expire user-defined custom urls.'),
      '#default_value' => '',
    );

    $elements['expire']['expire_node_custom_pages'] = array(
      '#type' => 'textarea',
      '#title' => t('Enter custom URLs'),
      '#description' => t('Enter one relative URL per line. It can be the path of a node (e.g. !example1) or of any alias (e.g. !example2). However, it has to be the final URL, not a redirect (use the !link1 and !link2 modules).', array('!example1' => '<strong>node/[node:nid]</strong>', '!example2' => '<strong>my/path</strong>', '!link1' => \Drupal::l('Global Redirect', Url::fromUri('https://drupal.org/project/globalredirect')), '!link2' => \Drupal::l('Redirect', Url::fromUri('https://drupal.org/project/redirect')))) . '<br/>'
        . t('If you want to match a path with any ending, add "|wildcard" to the end of the line (see !link1 for details). Example: !example1 will match !example1a, but also !example1b, !example1c, etc.', array('!link1' => \Drupal::l('function cache_clear_all', Url::fromUri('https://api.drupal.org/api/drupal/includes%21cache.inc/function/cache_clear_all/7')), '!example1' => '<strong>my/path|wildcard</strong>', '!example1a' => '<strong>my/path</strong>', '!example1b' => '<strong>my/path/one</strong>', '!example1c' => '<strong>my/path/two</strong>')) . '<br/>'
        . t('You may use tokens here.'),
      '#states' => array(
        'visible' => array(
          ':input[name="expire_node_custom"]' => array('checked' => TRUE),
        ),
      ),
      '#default_value' => '',
    );


    return $elements;
  }

}