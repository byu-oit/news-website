<?php

namespace Drupal\imageapi_optimize;

use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for configurable image optimize processors.
 *
 * @see \Drupal\imageapi_optimize\Annotation\ImageAPIOptimizeProcessor
 * @see \Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorBase
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorBase
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorManager
 * @see plugin_api
 */
interface ConfigurableImageAPIOptimizeProcessorInterface extends ImageAPIOptimizeProcessorInterface, PluginFormInterface {
}
