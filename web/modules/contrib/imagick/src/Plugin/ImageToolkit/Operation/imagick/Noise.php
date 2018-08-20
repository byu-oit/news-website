<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick noise operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_noise",
 *   toolkit = "imagick",
 *   operation = "noise",
 *   label = @Translation("Noise"),
 *   description = @Translation("Adds noise to the image.")
 * )
 */
class Noise extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'type' => [
        'description' => 'The type of noise being used.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->addNoiseImage($arguments['type']);
  }

}
