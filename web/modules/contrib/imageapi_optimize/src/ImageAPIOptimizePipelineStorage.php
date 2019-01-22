<?php

namespace Drupal\imageapi_optimize;

use Drupal\Core\Config\Entity\ConfigEntityStorage;

/**
 * Storage controller class for "image optimize pipeline" configuration entities.
 */
class ImageAPIOptimizePipelineStorage extends ConfigEntityStorage implements ImageAPIOptimizePipelineStorageInterface {

  /**
   * Image optimize pipeline replacement memory storage.
   *
   * This value is not stored in the backend. It's used during the deletion of
   * an image optimize pipeline to save the replacement image optimize pipeline in the same request. The
   * value is used later, when resolving dependencies.
   *
   * @var string[]
   *
   * @see \Drupal\imageapi_optimize\Form\ImageAPIOptimizePipelineDeleteForm::submitForm()
   */
  protected $replacement = [];

  /**
   * {@inheritdoc}
   */
  public function setReplacementId($name, $replacement) {
    $this->replacement[$name] = $replacement;
  }

  /**
   * {@inheritdoc}
   */
  public function getReplacementId($name) {
    return isset($this->replacement[$name]) ? $this->replacement[$name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function clearReplacementId($name) {
    unset($this->replacement[$name]);
  }

}
