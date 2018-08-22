<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick emboss operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_emboss",
 *   toolkit = "imagick",
 *   operation = "emboss",
 *   label = @Translation("Emboss"),
 *   description = @Translation("Applies the emboss effect on an image")
 * )
 */
class Emboss extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'radius' => [
        'description' => 'The radius of the emboss effect.',
      ],
      'sigma' => [
        'description' => 'The sigma of the emboss effect.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->embossImage($arguments['radius'], $arguments['sigma']);
  }

}
