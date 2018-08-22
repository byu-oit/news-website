<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick sketch operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_sketch",
 *   toolkit = "imagick",
 *   operation = "sketch",
 *   label = @Translation("Sketch"),
 *   description = @Translation("Generates a sketch from an image.")
 * )
 */
class Sketch extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'radius' => [
        'description' => 'The radius of the sketch.',
      ],
      'sigma' => [
        'description' => 'The sigma of the sketch.',
      ],
      'angle' => [
        'description' => 'The angle of the sketch.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->sketchImage($arguments['radius'], $arguments['sigma'], $arguments['angle']);
  }

}
