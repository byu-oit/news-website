<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick coloroverlay operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_coloroverlay",
 *   toolkit = "imagick",
 *   operation = "coloroverlay",
 *   label = @Translation("Coloroverlay"),
 *   description = @Translation("Applies the coloroverlay effect on an image")
 * )
 */
class Coloroverlay extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'HEX' => [
        'description' => 'The color used to create the overlay.',
      ],
      'alpha' => [
        'description' => 'The transparency of the overlay layer.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $color = new Imagick();
    $color->newImage($resource->getImageWidth(), $resource->getImageHeight(), $arguments['HEX']);
    $color->setImageOpacity($arguments['alpha'] / 100);

    return $resource->compositeImage($color, Imagick::COMPOSITE_DEFAULT, 0, 0);
  }

}
