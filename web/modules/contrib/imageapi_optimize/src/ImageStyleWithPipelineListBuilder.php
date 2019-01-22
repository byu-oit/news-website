<?php

namespace Drupal\imageapi_optimize;

use Drupal\Core\Entity\EntityInterface;
use Drupal\image\ImageStyleListBuilder;

/**
 * Defines a class to build a listing of image optimize pipeline entities.
 *
 * Adds a pipeline column to the table.
 *
 * @see \Drupal\image\Entity\ImageStyle
 */
class ImageStyleWithPipelineListBuilder extends ImageStyleListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();
    $header['pipeline'] = $this->t('Image Optimize Pipeline');

    // Move 'operations' to the end.
    $operations = $header['operations'];
    unset($header['operations']);
    $header['operations'] = $operations;
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\imageapi_optimize\Entity\ImageStyleWithPipeline $entity */
    $pipelineNames = imageapi_optimize_pipeline_options(FALSE);
    $row = parent::buildRow($entity);
    $row['pipeline'] = isset($pipelineNames[$entity->getPipeline()]) ? $pipelineNames[$entity->getPipeline()] : '';

    // Move 'operations' to the end.
    $operations = $row['operations'];
    unset($row['operations']);
    $row['operations'] = $operations;
    return $row;
  }

}
