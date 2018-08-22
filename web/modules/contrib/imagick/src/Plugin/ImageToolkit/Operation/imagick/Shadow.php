<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;
use ImagickPixel;

/**
 * Defines imagick shadow operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_shadow",
 *   toolkit = "imagick",
 *   operation = "shadow",
 *   label = @Translation("Shadow"),
 *   description = @Translation("Generates a shadow around an image.")
 * )
 */
class Shadow extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'color' => [
        'description' => 'The color of the shadow.',
      ],
      'opacity' => [
        'description' => 'The opacity of the shadow.',
      ],
      'sigma' => [
        'description' => 'The sigma of the shadow.',
      ],
      'x' => [
        'description' => 'The angle of the shadow.',
      ],
      'y' => [
        'description' => 'The angle of the shadow.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick &$resource, array $arguments) {
    $color = $arguments['color'];
    $opacity = $arguments['opacity'];
    $sigma = $arguments['sigma'];
    $x = $arguments['x'];
    $y = $arguments['y'];

    $color = empty($color) ? 'none' : $color;

    $shadow = clone $resource;
    $shadow->setImageBackgroundColor(new ImagickPixel($color));
    $shadow->shadowImage($opacity, $sigma, $x, $y);
    $success = $shadow->compositeImage($resource, Imagick::COMPOSITE_OVER, -$x + ($sigma * 2), -$y + ($sigma * 2));

    $resource = $shadow;

    return $success;
  }

}
