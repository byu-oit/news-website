<?php

namespace Drupal\imageapi_optimize\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an image optimize processor annotation object.
 *
 * Plugin Namespace: Plugin\ImageAPIOptimizeProcessor.
 *
 * For a working example, see
 * \Drupal\imageapi_optimize\Plugin\ImageAPIOptimizeProcessor\reSmushit
 *
 * @see hook_imageapi_optimize_processor_info_alter()
 * @see \Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorInterface
 * @see \Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorBase
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorBase
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorManager
 * @see plugin_api
 *
 * @Annotation
 */
class ImageAPIOptimizeProcessor extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the image optimize processor.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A brief description of the image optimize processor.
   *
   * This will be shown when adding or configuring this image optimize processor.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description = '';

}
