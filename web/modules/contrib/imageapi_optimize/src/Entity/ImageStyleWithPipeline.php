<?php

namespace Drupal\imageapi_optimize\Entity;

use Drupal\image\Entity\ImageStyle;

/**
 *
 */
class ImageStyleWithPipeline extends ImageStyle {

  /**
   * @TODO: maybe this should be something other than a 'random' string.
   */
  protected $pipeline = '__default__';

  /**
   * {@inheritdoc}
   */
  public function createDerivative($original_uri, $derivative_uri) {
    $result = parent::createDerivative($original_uri, $derivative_uri);

    if ($result) {
      // Apply the pipeline to the $derivative_uri.
      if ($this->hasPipeline()) {
        $this->getPipelineEntity()->applyToImage($derivative_uri);
      }
    }

    // Always return the result of the parent class processing.
    return $result;
  }

  /**
   *
   */
  public function getPipeline() {
    return $this->pipeline;
  }

  /**
   * @return \Drupal\imageapi_optimize\Entity\ImageAPIOptimizePipeline|null
   */
  public function getPipelineEntity() {
    if (!empty($this->pipeline)) {
      $storage = $this->entityTypeManager()->getStorage('imageapi_optimize_pipeline');
      if ($this->pipeline == '__default__') {
        if ($default_pipeline_name = \Drupal::config('imageapi_optimize.settings')->get('default_pipeline')) {
          $pipelineId = $default_pipeline_name;
        }
      }
      else {
        $pipelineId = $this->pipeline;
      }
      if (!empty($pipelineId) && ($pipeline = $storage->load($pipelineId))) {
        return $pipeline;
      }
    }
  }

  /**
   *
   */
  public function hasPipeline() {
    return (bool) $this->getPipelineEntity();
  }

}
