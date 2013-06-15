<?php

/**
 * @file
 * Provides class that expires nodes.
 */

class ExpireNode implements ExpireInterface {

  /**
   * Executes expiration actions for node.
   *
   * @param $node
   *   Node object.
   *
   * @param $action
   *   Action that has been executed.
   */
  function expire($node, $action) {

    if (empty($node->nid) || empty($node->type)) {
      return;
    }

    // See if cache settings was overridden for this node type.
    $settings_overridden = variable_get('expire_node_override_defaults_' . $node->type);

    $variable_suffix = '';
    if (!empty($settings_overridden)) {
      // If page cache settings was overridden for this node type we
      // should add "_[NODE-TYPE]" to every variable name we use here.
      $variable_suffix = '_' . $node->type;
    }

    $enabled_actions = variable_get('expire_node_actions' . $variable_suffix, array());
    $enabled_actions = array_filter($enabled_actions);

    // Do not process current action if it is not selected by administrator.
    if (!in_array($action, $enabled_actions)) {
      return;
    }

    $expire_urls = array();

    // Expire front page.
    $expire_front_page = variable_get('expire_node_front_page' . $variable_suffix, EXPIRE_NODE_FRONT_PAGE);
    if ($expire_front_page) {
      $expire_urls['front'] = '<front>';
    }

    // Expire node page.
    $expire_node_page = variable_get('expire_node_node_page' . $variable_suffix, EXPIRE_NODE_NODE_PAGE);
    if ($expire_node_page) {
      $expire_urls['node-' . $node->nid] = 'node/' . $node->nid;
    }

    // Expire taxonomy term pages.
    $expire_term_pages = variable_get('expire_node_term_pages' . $variable_suffix, EXPIRE_NODE_TERM_PAGES);
    if (module_exists('taxonomy') && $expire_term_pages) {
      $urls = ExpireAPI::expireNodeTermPages($node);
      $expire_urls = array_merge($expire_urls, $urls);
    }

    // Expire node reference's pages.
    $expire_node_references = variable_get('expire_node_reference_pages' . $variable_suffix, EXPIRE_NODE_REFERENCE_PAGES);
    if (module_exists('node_reference') && $expire_node_references) {
      $urls = ExpireAPI::expireNodeReferences($node);
      $expire_urls = array_merge($expire_urls, $urls);
    }

    // Expire custom pages.
    $expire_custom = variable_get('expire_node_custom' . $variable_suffix, EXPIRE_NODE_CUSTOM);
    if ($expire_custom) {
      $pages = variable_get('expire_node_custom_pages' . $variable_suffix);
      $urls = ExpireAPI::expireCustomPages($pages, array('node' => $node));
      $expire_urls = array_merge($expire_urls, $urls);
    }

    // Flush page cache for expired urls.
    if (!empty($expire_urls)) {
      ExpireAPI::flushCache($expire_urls, 'node', $node);
    }
  }

}