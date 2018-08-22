<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick solarize operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_solarize",
 *   toolkit = "imagick",
 *   operation = "solarize",
 *   label = @Translation("Solarize"),
 *   description = @Translation("Solarizes an image.")
 * )
 */
class Solarize extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'threshold' => [
        'description' => 'The threshold of the solarize effect.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->solarizeImage($arguments['threshold']);
  }

}
