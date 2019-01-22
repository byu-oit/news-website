<?php

namespace Drupal\imageapi_optimize;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an image optimize pipeline entity.
 */
interface ImageAPIOptimizePipelineInterface extends ConfigEntityInterface {

  /**
   * Returns the replacement ID.
   *
   * @return string|null
   *   The replacement image optimize pipeline ID or NULL if no replacement has been
   *   selected.
   *
   * @deprecated in Drupal 8.0.x, will be removed before Drupal 9.0.x. Use
   *   \Drupal\imageapi_optimize\ImageAPIOptimizePipelineStorageInterface::getReplacementId() instead.
   *
   * @see \Drupal\imageapi_optimize\ImageAPIOptimizePipelineStorageInterface::getReplacementId()
   */
  public function getReplacementID();

  /**
   * Returns the image optimize pipeline.
   *
   * @return string
   *   The name of the image optimize pipeline.
   */
  public function getName();

  /**
   * Sets the name of the image optimize pipeline.
   *
   * @param string $name
   *   The name of the image optimize pipeline.
   *
   * @return \Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface
   *   The class instance this method is called on.
   */
  public function setName($name);

  /**
   * Returns a specific image optimize processor.
   *
   * @param string $processor
   *   The image optimize processor ID.
   *
   * @return \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface
   *   The image optimize processor object.
   */
  public function getProcessor($processor);

  /**
   * Returns the image optimize processors for this pipeline.
   *
   * The processors should be sorted, and will have been instantiated.
   *
   * @return \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorPluginCollection|\Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface[]
   *   The image optimize processor plugin collection.
   */
  public function getProcessors();

  /**
   * Returns an image optimize processors collection.
   *
   * @return \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorPluginCollection|\Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface[]
   *   The image optimize processor plugin collection.
   */
  public function getProcessorsCollection();

  /**
   * Saves an image optimize processor for this pipeline.
   *
   * @param array $configuration
   *   An array of image optimize processor configuration.
   *
   * @return string
   *   The image optimize processor ID.
   */
  public function addProcessor(array $configuration);

  /**
   * Deletes an image optimize processor from this pipeline.
   *
   * @param \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface $processor
   *   The image optimize processor object.
   *
   * @return $this
   */
  public function deleteProcessor(ImageAPIOptimizeProcessorInterface $processor);

  /**
   * Flushes cached media for this pipeline.
   *
   * @return $this
   */
  public function flush();

  /**
   * Creates a new image derivative based on this image optimize pipeline.
   *
   * Generates an image derivative applying all image optimize processors and saving the
   * resulting image.
   *
   * @param string $image_uri
   *   Original image file URI.
   *
   * @return bool
   *   TRUE if an image derivative was generated, or FALSE if the image
   *   derivative could not be generated.
   */
  public function applyToImage($image_uri);

}
