<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;

/**
 * Defines imagick blur operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_composite",
 *   toolkit = "imagick",
 *   operation = "composite",
 *   label = @Translation("Composite"),
 *   description = @Translation("Composite one image onto another at the specified offset.")
 * )
 */
class Composite extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'path' => [
        'description' => 'Path to the composite image',
      ],
      'composite' => [
        'description' => 'Composite operator',
      ],
      'x' => [
        'description' => 'The column offset of the composited image',
      ],
      'y' => [
        'description' => 'he row offset of the composited image',
      ],
      'channel' => [
        'description' => 'Provide any channel constant that is valid for your channel mode. It is possible to apply more than one channel.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    // Get the correct path
    $path = $arguments['path'];
    if (!UrlHelper::isExternal($path)) {
      $path = drupal_realpath($path);
    }

    // Get the composite image
    $composite = new Imagick($path);

    // Create channel using bitwise operator
    $channel = array_reduce($arguments['channel'], function($a, $b) { return $a | $b; }, 0);

    return $resource->compositeImage($composite, $arguments['composite'], $arguments['x'], $arguments['y'], $channel);
  }

}