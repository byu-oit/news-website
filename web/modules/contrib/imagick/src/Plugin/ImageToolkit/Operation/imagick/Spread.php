<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick spread operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_spread",
 *   toolkit = "imagick",
 *   operation = "spread",
 *   label = @Translation("Spread"),
 *   description = @Translation("Adds spread to an image.")
 * )
 */
class Spread extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'radius' => [
        'description' => 'The color of the shadow.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->spreadImage($arguments['radius']);
  }

}
