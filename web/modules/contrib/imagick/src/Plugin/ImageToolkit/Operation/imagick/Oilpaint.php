<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick oilpaint operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_oilpaint",
 *   toolkit = "imagick",
 *   operation = "oilpaint",
 *   label = @Translation("Oilpaint"),
 *   description = @Translation("Oilpaints the image.")
 * )
 */
class Oilpaint extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'radius' => [
        'description' => 'The threshold of the oilpaint effect.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->oilPaintImage($arguments['radius']);
  }

}
