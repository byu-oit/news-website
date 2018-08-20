<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick sharpen operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_sharpen",
 *   toolkit = "imagick",
 *   operation = "sharpen",
 *   label = @Translation("Sharpen"),
 *   description = @Translation("Applies the sharpen effect on an image")
 * )
 */
class Sharpen extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'radius' => [
        'description' => 'The radius of the Gaussian, in pixels, not counting the center pixel. Use 0 for auto-select.',
      ],
      'sigma' => [
        'description' => 'The standard deviation of the Gaussian, in pixels.',
      ],
      'amount' => [
        'description' => 'The fraction of the difference between the original and the blur image that is added back into the original.',
      ],
      'threshold' => [
        'description' => 'The threshold, as a fraction of QuantumRange, needed to apply the difference amount.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->unsharpMaskImage($arguments['radius'], $arguments['sigma'], $arguments['amount'], $arguments['threshold']);
  }

}
