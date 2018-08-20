<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick decipher operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_decipher",
 *   toolkit = "imagick",
 *   operation = "decipher",
 *   label = @Translation("Decipher"),
 *   description = @Translation("Applies the decipher effect on an image")
 * )
 */
class Decipher extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [
      'password' => [
        'description' => 'The password to decrypt the image with.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->decipherImage($arguments['password']);
  }

}
