<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick mirror operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_mirror",
 *   toolkit = "imagick",
 *   operation = "mirror",
 *   label = @Translation("Mirror"),
 *   description = @Translation("Mirrors the image.")
 * )
 */
class Mirror extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'flip' => [
        'description' => 'Mirror image verticaly.',
      ],
      'flop' => [
        'description' => 'Mirror image horizontaly.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    $flipSuccess = $flopSuccess = TRUE;
    if ($arguments['flip']) {
      $flipSuccess = $resource->flipImage();
    }
    if ($arguments['flop']) {
      $flopSuccess = $resource->flopImage();
    }

    return ($flipSuccess && $flopSuccess);
  }

}
