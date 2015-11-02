<?php
/**
 * @file
 * Contains ExpireComponentManager.
 */

namespace Drupal\expire;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Expire component plugin manager.
 */
class ExpireComponentManager extends DefaultPluginManager {

  /**
   * Constructs an ExpireComponentManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ExpireComponent', $namespaces, $module_handler, 'Drupal\expire\ExpireComponentInterface', 'Drupal\expire\Annotation\ExpireComponent');

    $this->alterInfo('expire_component');
    $this->setCacheBackend($cache_backend, 'expire_component');
  }
}