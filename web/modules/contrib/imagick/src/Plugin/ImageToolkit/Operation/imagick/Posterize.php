<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick solarize operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_posterize",
 *   toolkit = "imagick",
 *   operation = "posterize",
 *   label = @Translation("Posterize"),
 *   description = @Translation("Posterizes an image.")
 * )
 */
class Posterize extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'colors' => [
        'description' => 'Color levels per channel.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->posterizeImage($arguments['colors'], TRUE);
  }

}
