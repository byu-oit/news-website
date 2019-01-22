<?php

namespace Drupal\imageapi_optimize\Exception;

use Drupal\Component\Plugin\Exception\PluginNotFoundException as CorePluginNotFoundException;

/**
 * Plugin not found exception so we can retrieve the plugin ID
 *
 * @TOOD: Get this change into Drupal core.
 *
 * @see \Drupal\Component\Plugin\Exception\PluginNotFoundException
 */
class PluginNotFoundException extends CorePluginNotFoundException {

  protected $pluginId;

  /**
   * @inheritDoc
   */
  public function __construct($plugin_id, $message = '', $code = 0, \Exception $previous = NULL) {
    parent::__construct($plugin_id, $message, $code, $previous);
    $this->pluginId = $plugin_id;
  }

  /**
   * @return mixed
   */
  public function getPluginId() {
    return $this->pluginId;
  }
}