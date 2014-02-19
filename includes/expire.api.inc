<?php

/**
 * @file
 * Provides internal API for page cache flushes.
 */

class ExpireAPI {

  /**
   * Executes internal or external cache expiration.
   *
   * @param $urls
   *   List of internal or external urls that should be expired.
   *
   * @param $object_type
   *  Name of object type ('node', 'comment', 'user', etc).
   *
   * @param $object
   *   Object (node, comment, user, etc) for which expiration is executes.
   *
   * @param $absolute_urls_passed
   *   Indicates whether absolute URLs or internal paths were passed.
   */
  public static function executeExpiration($urls, $object_type = '', $object = NULL, $absolute_urls_passed = FALSE) {

    // Allow other modules to modify the list prior to expiring.
    drupal_alter('expire_cache', $urls, $object_type, $object, $absolute_urls_passed);

    // Nothing to expire, so exit.
    if (empty($urls)) {
      return;
    }

    // If was passed array with absolute URLs we should not do any job here.
    if ($absolute_urls_passed) {
      $wildcards = array();
      $absolute_urls = $urls;
    }
    else {
      list($absolute_urls, $wildcards) = self::convertToAbsoluteUrls($urls, entity_language($object_type, $object));
    }

    // Write some debug information.
    self::debugLog($absolute_urls, $wildcards, $object_type);

    $status = variable_get('expire_status', EXPIRE_STATUS_DISABLED);
    if ($status == EXPIRE_STATUS_ENABLED_INTERNAL) {
      self::executeInternalExpiration($absolute_urls, $wildcards);
    }
    elseif ($status == EXPIRE_STATUS_ENABLED_EXTERNAL) {
      self::executeExternalExpiration($absolute_urls, $wildcards, $object_type, $object);
    }
  }

  /**
   * Execute internal urls expiration.
   * Calls cache_clear_all().
   *
   * @param $urls
   *   List of absolute urls that should be flushed.
   *
   * @param $wildcards
   *   List of paths and its wildcard flushes.
   */
  protected static function executeInternalExpiration($urls, $wildcards) {
    foreach ($urls as $internal_path => $absolute_url) {

      // Check if wildcard is enabled for this URL.
      $wildcard = !empty($wildcards[$internal_path]);

      // Clear cached page data.
      cache_clear_all($absolute_url, 'cache_page', $wildcard);
    }
  }

  /**
   * Executes hook_cache_expire().
   *
   * It allows other modules to implement custom login for cache expiration.
   *
   * @param $urls
   *   List of absolute urls that should be flushed.
   *
   * @param $wildcards
   *   List of paths and its wildcard flushes.
   *
   * @param $object_type
   *  Name of object type ('node', 'comment', 'user', etc).
   *
   * @param $object
   *   Object (node, comment, user, etc) for which expiration is executes.
   */
  protected static function executeExternalExpiration($urls, $wildcards, $object_type, $object) {
    $modules = module_implements('expire_cache');
    foreach ($modules as $module) {
      module_invoke($module, 'expire_cache', $urls, $wildcards, $object_type, $object);
    }
  }

  /**
   * Find all taxonomy terms in the entity fields and build urls for them.
   *
   * @param $entity
   *   Entity object.
   *
   * @param $entity_type
   *   Type of entity.
   *
   * @return array
   *   Term urls that should be flushed.
   */
  public static function expireTermPages($entity, $entity_type) {

    $terms = array();
    list($id, $vid, $bundle_name) = entity_extract_ids($entity_type, $entity);
    $field_instances = field_info_instances($entity_type, $bundle_name);

    foreach ($field_instances as $field_name => $field_instance) {

      // Load information about field.
      $field_info = field_info_field($field_name);

      if ($field_info['type'] == 'taxonomy_term_reference') {
        $new_terms = field_get_items($entity_type, $entity, $field_name);
        if (is_array($new_terms) && !empty($new_terms)) {
          $terms = array_merge($new_terms, $terms);
        }
        $old_terms = !empty($entity->original) ? field_get_items($entity_type, $entity->original, $field_name) : array();
        if (is_array($old_terms) && !empty($old_terms)) {
          $terms = array_merge($old_terms, $terms);
        }
      }
    }

    $urls = array();
    foreach ($terms as $term) {
      $urls['term-' . $term['tid']] = 'taxonomy/term/' . $term['tid'];
    }

    return $urls;
  }

  /**
   * Find all entity references in fields and build urls for them.
   *
   * @param $entity
   *   Entity object.
   *
   * @param $entity_type
   *   Type of entity.
   *
   * @return array
   *   Entity references' urls that should be flushed.
   */
  public static function expireReferences($entity, $entity_type) {

    $field_references = array();

    list($id, $vid, $bundle_name) = entity_extract_ids($entity_type, $entity);

    // Get all fields from current entity type.
    $field_instances = field_info_instances($entity_type, $bundle_name);

    foreach ($field_instances as $field_name => $field_instance) {

      // Load information about field.
      $field_info = field_info_field($field_name);

      if (in_array($field_info['type'], array('node_reference', 'user_reference', 'entityreference'))) {

        if (in_array($field_info['type'], array('node_reference', 'user_reference'))) {
          $field_value_key = $field_info['type'] == 'node_reference' ? 'nid' : 'uid';
          $field_entity_type = $field_info['type'] == 'node_reference' ? 'node' : 'user';
        }
        else {
          $field_value_key = 'target_id';
          $field_entity_type = $field_info['settings']['target_type'];
        }

        // Get new values from reference field.
        $new_references = field_get_items($entity_type, $entity, $field_name);

        // Get old values from reference field.
        $old_references = !empty($entity->original) ? field_get_items($entity_type, $entity->original, $field_name) : array();

        $references = array();
        if (!empty($new_references) && is_array($new_references)) {
          $references = array_merge($references, $new_references);
        }
        if (!empty($old_references) && is_array($old_references)) {
          $references = array_merge($references, $old_references);
        }

        foreach ($references as $reference) {
          $field_entity_id = isset($reference[$field_value_key]) ? $reference[$field_value_key] : FALSE;
          $field_references[$field_entity_type . $field_entity_id] = array(
            'entity_type' => $field_entity_type,
            'entity_id' => $field_entity_id,
          );
        }

      }
    }

    // Collect urls of referenced entities.
    $urls = array();
    foreach ($field_references as $field_reference) {

      // Load entity.
      $field_entity = entity_load_single($field_reference['entity_type'], $field_reference['entity_id']);
      if (empty($field_entity)) {
        continue;
      }

      // Get entity uri;
      $field_entity_uri = entity_uri($field_reference['entity_type'], $field_entity);
      if (empty($field_entity_uri['path'])) {
        continue;
      }

      $urls['reference-' . $field_reference['entity_type'] . '-' . $field_reference['entity_id']] = $field_entity_uri['path'];
    }

    return $urls;
  }

  /**
   * Create expiration urls for custom pages.
   *
   * @param $pages
   *   Unformated string from user input raw.
   *
   * @param $token_options
   *   Options for token replacements.
   *
   * @return array
   *   List of custom urls that should be flushed.
   */
  public static function expireCustomPages($pages, $token_options) {

    $urls = array();

    $pages = explode("\n", $pages);
    foreach ($pages as $index => $page) {
      $page = trim($page);
      if (!empty($page)) {

        // Replace only urls with tokens.
        if (token_scan($page)) {
          $page = token_replace($page, $token_options);
        }

        $urls['custom-' . $index] = $page;
      }
    }

    return $urls;
  }

  /**
   * Convert internal path to absolute URLs.
   *
   * @param $internal_paths
   *   Array of internal paths.
   *
   * @param $langcode
   *   The language code that we'll use to lookup the alias.
   *
   * @return array
   */
  protected static function convertToAbsoluteUrls($internal_paths, $langcode = NULL) {

    $absolute_urls = array();
    $wildcards = array();

    foreach ($internal_paths as $path) {
      $wildcard = FALSE;

      // Every URL may contain |wildcard suffix, so we should check this.
      $path_parts = explode('|', $path);
      if (sizeof($path_parts) > 1 && $path_parts[sizeof($path_parts) - 1] == 'wildcard') {
        $wildcard = TRUE;
        array_pop($path_parts); // Remove 'wildcard' from path.
        $path = implode('|', $path_parts);
      }

      // Collect array with information about expired URLs and its wildcards.
      // Don't convert this to an alias now since we'll do that later on.
      $absolute_urls[$path] = url($path, array('absolute' => TRUE, 'alias' => TRUE));
      $wildcards[$path] = $wildcard;

      // Get the path alias for this path, and add it to the array if one was
      // found.
      $alias = drupal_get_path_alias($path, $langcode);
      if ($alias != $path) {
        $absolute_urls[$alias] = url($alias, array('absolute' => TRUE, 'alias' => TRUE));
      }
    }

    return array($absolute_urls, $wildcards);
  }

  /**
   * Log debug information.
   *
   * @param $absolute_urls
   * @param $wildcards
   * @param $object_type
   */
  protected static function debugLog($absolute_urls, $wildcards, $object_type) {

    $debug = variable_get('expire_debug', EXPIRE_DEBUG_DISABLED);
    if (empty($debug)) {
      return;
    }

    $output_urls = array();
    foreach ($absolute_urls as $internal_path => $url) {
      $wildcard = !empty($wildcards[$internal_path]) ? 'true' : 'false';
      $output_urls[] = t('URL: @url', array('@url' => check_url($url)));
      $output_urls[] = t('Wildcard: @wildcard', array('@wildcard' => $wildcard));
      $output_urls[] = t('Expired object: @type', array('@type' => $object_type ? $object_type : '(none)'));
      $output_urls[] = '--------';
    }

    // Log debug message in watchdog.
    $message = t('Expiration was executed for the next URLs: !urls', array('!urls' => theme('item_list', array('items' => $output_urls))));
    watchdog('expire', $message, array(), WATCHDOG_DEBUG);

    // For development might be useful to print info on screen.
    if ($debug == EXPIRE_DEBUG_FULL) {
      drupal_set_message($message);
    }
  }

}
