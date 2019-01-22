<?php

namespace Drupal\imageapi_optimize;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\imageapi_optimize\Exception\PluginNotFoundException;

/**
 * Manages image optimize processor plugins.
 *
 * @see hook_imageapi_optimize_processor_info_alter()
 * @see \Drupal\imageapi_optimize\Annotation\ImageAPIOptimizeProcessor
 * @see \Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorInterface
 * @see \Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorBase
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorBase
 * @see plugin_api
 */
class ImageAPIOptimizeProcessorManager extends DefaultPluginManager {

  /**
   * Constructs a new ImageAPIOptimizeProcessorManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ImageAPIOptimizeProcessor', $namespaces, $module_handler, 'Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface', 'Drupal\imageapi_optimize\Annotation\ImageAPIOptimizeProcessor');

    $this->alterInfo('imageapi_optimize_processor_info');
    $this->setCacheBackend($cache_backend, 'imageapi_optimize_processor_plugins');
  }

  /**
   * Gets a specific plugin definition.
   *
   * @param array $definitions
   *   An array of the available plugin definitions.
   * @param string $plugin_id
   *   A plugin id.
   * @param bool $exception_on_invalid
   *   If TRUE, an invalid plugin ID will cause an exception to be thrown; if
   *   FALSE, NULL will be returned.
   *
   * @return array|null
   *   A plugin definition, or NULL if the plugin ID is invalid and
   *   $exception_on_invalid is TRUE.
   *
   * @throws \Drupal\imageapi_optimize\Exception\PluginNotFoundException
   *   Thrown if $plugin_id is invalid and $exception_on_invalid is TRUE.
   */
  protected function doGetDefinition(array $definitions, $plugin_id, $exception_on_invalid) {
    // Avoid using a ternary that would create a copy of the array.
    if (isset($definitions[$plugin_id])) {
      return $definitions[$plugin_id];
    }
    elseif (!$exception_on_invalid) {
      return NULL;
    }

    throw new PluginNotFoundException($plugin_id, sprintf('The "%s" plugin does not exist.', $plugin_id));
  }

}
