<?php

namespace Drupal\imageapi_optimize;

/**
 * Interface for storage controller for "imageapi optimize pipeline" configuration entities.
 */
interface ImageAPIOptimizePipelineStorageInterface {

  /**
   * Stores a replacement ID for an image optimize pipeline being deleted.
   *
   * The method stores a replacement pipeline to be used by the configuration
   * dependency system when a image optimize pipeline is deleted. The replacement pipeline is
   * replacing the deleted pipeline in other configuration entities that are
   * depending on the image optimize pipeline being deleted.
   *
   * @param string $name
   *   The ID of the image optimize pipeline to be deleted.
   * @param string $replacement
   *   The ID of the image optimize pipeline used as replacement.
   */
  public function setReplacementId($name, $replacement);

  /**
   * Retrieves the replacement ID of a deleted image optimize pipeline.
   *
   * The method is retrieving the value stored by ::setReplacementId().
   *
   * @param string $name
   *   The ID of the image optimize pipeline to be replaced.
   *
   * @return string|null
   *   The ID of the image optimize pipeline used as replacement, if there's any, or NULL.
   *
   * @see \Drupal\imageapi_optimize\ImageAPIOptimizePipelineStorageInterface::setReplacementId()
   */
  public function getReplacementId($name);

  /**
   * Clears a replacement ID from the storage.
   *
   * The method clears the value previously stored with ::setReplacementId().
   *
   * @param string $name
   *   The ID of the image optimize pipeline to be replaced.
   *
   * @see \Drupal\imageapi_optimize\ImageAPIOptimizePipelineStorageInterface::setReplacementId()
   */
  public function clearReplacementId($name);

}
