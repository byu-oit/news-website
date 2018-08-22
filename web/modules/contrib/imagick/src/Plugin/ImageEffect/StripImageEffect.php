<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Image\ImageInterface;
use Drupal\image\ImageEffectBase;

/**
 * Strips an image of all profiles and comments.
 *
 * @ImageEffect(
 *   id = "image_strip",
 *   label = @Translation("Strip"),
 *   description = @Translation("Strips an image of all profiles and comments.")
 * )
 */
class StripImageEffect extends ImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('strip')) {
      $this->logger->error('Image strip failed using the %toolkit toolkit on %path (%mimetype)', [
        '%toolkit' => $image->getToolkitId(),
        '%path' => $image->getSource(),
        '%mimetype' => $image->getMimeType()
      ]);

      return FALSE;
    }

    return TRUE;
  }

}
