<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;

/**
 * Defines imagick strip operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_strip",
 *   toolkit = "imagick",
 *   operation = "strip",
 *   label = @Translation("Strip"),
 *   description = @Translation("Strips an image of all profiles and comments.")
 * )
 */
class Strip extends ImagickOperationBase {

  /**
   * {@inheritdoc}
   */
  protected function arguments() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function process(Imagick $resource, array $arguments) {
    return $resource->stripImage();
  }

}
