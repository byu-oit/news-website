<?php

namespace Drupal\imagick\Plugin\ImageToolkit\Operation\imagick;

use Drupal\imagick\ImagickException;
use Imagick;

/**
 * Class ImagickOperationTrait
 */
trait ImagickOperationTrait {

  /**
   * {@inheritdoc}
   */
  protected function execute(array $arguments) {
    return $this->processFrames($arguments);
  }

  /**
   * Process image frames for GIFs
   *
   * @param array $arguments
   * @return bool
   */
  protected function processFrames(array $arguments = []) {
    /* @var $resource Imagick */
    $resource = $this->getToolkit()->getResource();

    // If preferred format is set, use it as prefix for writeImage
    // If not this will throw a ImagickException exception
    try {
      $image_format = $resource->getImageFormat();
    } catch (ImagickException $e) {}

    $success = TRUE;
    if (isset($image_format) && in_array($image_format, ['GIF'])) {
      // Get each frame in the GIF
      $resource = $resource->coalesceImages();
      do {
        if (!$this->process($resource, $arguments)) {
          $success = FALSE;
          break;
        }
      } while ($resource->nextImage());

      $resource->deconstructImages();
    }
    else {
      $success = $this->process($resource, $arguments);
    }

    // Set the processed resource
    $this->getToolkit()->setResource($resource);

    return $success;
  }

}
