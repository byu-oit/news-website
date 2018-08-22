<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick modulate operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_modulate",
 *   toolkit = "imagick",
 *   operation = "modulate",
 *   label = @Translation("Modulate"),
 *   description = @Translation("Modulates the image.")
 * )
 */
class Modulate extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'brightness' => [
        'description' => 'Brightness in percentage.',
      ],
      'saturation' => [
        'description' => 'Saturation in percentage.',
      ],
      'hue' => [
        'description' => 'Hue in percentage.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->modulateImage($arguments['brightness'], $arguments['saturation'], $arguments['hue']);
  }

}
