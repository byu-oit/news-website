<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick swirl operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_swirl",
 *   toolkit = "imagick",
 *   operation = "swirl",
 *   label = @Translation("Swirl"),
 *   description = @Translation("Adds a swirl effect to an image.")
 * )
 */
class Swirl extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'degrees' => [
        'description' => 'The amplitude of the wave effect.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->swirlImage($arguments['degrees']);
  }

}
