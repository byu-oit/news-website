<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Imagick;
use ImagickPixel;

/**
 * Defines imagick autorotate operation.
 *
 * @ImageToolkitOperation(
 *   id = "imagick_autorotate",
 *   toolkit = "imagick",
 *   operation = "autorotate",
 *   label = @Translation("Autorotate"),
 *   description = @Translation("Autorotates an image using EXIF data.")
 * )
 */
class Autorotate extends ImagickOperationBase {

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
    $orientation = $resource->getImageOrientation();

    // See https://stackoverflow.com/a/40055711/8048794
    // First rotate to correct position
    switch ($orientation) {
      case Imagick::ORIENTATION_BOTTOMRIGHT:
      case Imagick::ORIENTATION_BOTTOMLEFT:
        $resource->rotateimage(new ImagickPixel(), 180); // rotate 180 degrees
        break;
      case Imagick::ORIENTATION_RIGHTTOP:
      case Imagick::ORIENTATION_LEFTTOP:
        $resource->rotateimage(new ImagickPixel(), 90); // rotate 90 degrees CW
        break;
      case Imagick::ORIENTATION_LEFTBOTTOM:
      case Imagick::ORIENTATION_RIGHTBOTTOM:
        $resource->rotateimage(new ImagickPixel(), -90); // rotate 90 degrees CCW
        break;
    }

    // Flop image if required
    if (in_array($orientation, array(
      Imagick::ORIENTATION_TOPRIGHT,
      Imagick::ORIENTATION_BOTTOMLEFT,
      Imagick::ORIENTATION_LEFTTOP,
      Imagick::ORIENTATION_RIGHTBOTTOM))) {
      $resource->flopImage();
    }

    // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
    return $resource->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
  }

}
