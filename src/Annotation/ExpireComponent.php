<?php
/**
 * @file
 * Contains \Drupal\expire\Annotation\ExpireComponent.
 */

namespace Drupal\expire\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Expire Component annotation object.
 *
 * Plugin Namespace: Plugin\expire\ExpireComponent
 *
 * @see \Drupal\expire\ExpireComponentManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExpireComponent extends Plugin {

    /**
     * The plugin ID.
     *
     * @var string
     */
    public $id;

    /**
     * The name of the component.
     *
     * @var \Drupal\Core\Annotation\Translation
     *
     * @ingroup plugin_translatable
     */
    public $label;
}