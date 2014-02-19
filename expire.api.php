<?php

/**
 * @file
 * API documentation for Cache Expiration module.
 */

/**
 * Provides possibility to flush pages for external cache storages.
 *
 * @param $urls
 *   List of internal paths and/or absolute URLs that should be flushed.
 *
 *   Example of array (when base url include option is enabled):
 *   array(
 *     'node/1' => 'http://example.com/node/1',
 *     'news' => 'http://example.com/news',
 *   );
 *
 *   Example of array (when base url include option is disabled):
 *   array(
 *     'node/1' => 'node/1',
 *     'news' => 'news',
 *   );
 *
 * @param $wildcards
 *   Array with wildcards implementations for each internal path.
 *   Indicates whether should be executed wildcard cache flush.
 *
 *   Example:
 *   array(
 *     'node/1' => FALSE,
 *     'news' => TRUE,
 *   );
 *
 * @param $object_type
 *  Name of object type ('node', 'comment', 'user', etc).
 *
 * @param $object
 *   Object (node, comment, user, etc) for which expiration is executes.
 *
 * @see expire.api.inc
 */
function hook_expire_cache($urls, $wildcards, $object_type, $object) {
  foreach ($urls as $url) {
    $full_path = url($url, array('absolute' => TRUE));
    purge_urls($full_path, $wildcards);
  }
}

/**
 * Provides possibility to change urls before they are expired.
 *
 * @param $urls
 *   List of internal paths and/or absolute URLs that should be flushed.
 *
 *   Example of array (when base url include option is enabled):
 *   array(
 *     'node/1' => 'http://example.com/node/1',
 *     'news' => 'http://example.com/news',
 *   );
 *
 *   Example of array (when base url include option is disabled):
 *   array(
 *     'node/1' => 'node/1',
 *     'news' => 'news',
 *   );
 *
 * @param $object_type
 *  Name of object type ('node', 'comment', 'user', etc).
 *
 * @param $object
 *   Object (node, comment, user, etc) for which expiration is executes.
 *
 * @see expire.api.inc
 */
function hook_expire_cache_alter(&$urls, $object_type, $object) {
  if ($object_type == 'node') {
    unset($urls['node-' . $object->nid]);
    $urls['example'] = 'custom_page/' . $object->uid . '/' . $object->nid;
  }
}
