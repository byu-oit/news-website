<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick vignette operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_vignette",
 *   toolkit = "imagick",
 *   operation = "vignette",
 *   label = @Translation("Vignette"),
 *   description = @Translation("Adds vignette to an image.")
 * )
 */
class Vignette extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'blackpoint' => [
        'description' => 'The black point.',
      ],
      'whitepoint' => [
        'description' => 'The white point.',
      ],
      'x' => [
        'description' => 'The X offset of the ellipse.',
      ],
      'y' => [
        'description' => 'The Y offset of the ellipse.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->vignetteImage($arguments['blackpoint'], $arguments['whitepoint'], $arguments['x'], $arguments['y']);
  }

}
