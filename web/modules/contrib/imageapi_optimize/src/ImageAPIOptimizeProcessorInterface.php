<?php

namespace Drupal\imageapi_optimize;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for image optimize processors.
 *
 * @see \Drupal\imageapi_optimize\Annotation\ImageAPIOptimizeProcessor
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorBase
 * @see \Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorInterface
 * @see \Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorBase
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorManager
 * @see plugin_api
 */
interface ImageAPIOptimizeProcessorInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Returns a render array summarizing the configuration of the image optimize processor.
   *
   * @return array
   *   A render array.
   */
  public function getSummary();

  /**
   * Returns the image optimize processor label.
   *
   * @return string
   *   The image optimize processor label.
   */
  public function label();

  /**
   * Returns the unique ID representing the image optimize processor.
   *
   * @return string
   *   The image optimize processor ID.
   */
  public function getUuid();

  /**
   * Returns the weight of the image optimize processor.
   *
   * @return int|string
   *   Either the integer weight of the image optimize processor, or an empty string.
   */
  public function getWeight();

  /**
   * Sets the weight for this image optimize processor.
   *
   * @param int $weight
   *   The weight for this image optimize processor.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Apply this image optimize processor to the given image.
   *
   * Image processors should modify the file in-place or overwrite the file on
   * disk with an optimized version.
   *
   * @param string $image_uri
   *   Original image file URI.
   *
   * @return bool
   *   TRUE if an optimized image was generated, or FALSE if the image
   *   could not be optimized.
   */
  public function applyToImage($image_uri);

}
