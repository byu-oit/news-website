<?php

namespace Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor;

use Drupal\imageapi_optimize_binaries\ImageAPIOptimizeProcessorBinaryBase;

/**
 * Uses the PngQuant binary to optimize images.
 *
 * @ImageAPIOptimizeProcessor(
 *   id = "pngquant",
 *   label = @Translation("PngQuant"),
 *   description = @Translation("Uses the PngQuant binary to optimize images.")
 * )
 */
class PngQuant extends ImageAPIOptimizeProcessorBinaryBase {

  /**
   * {@inheritdoc}
   */
  protected function executableName() {
    return 'pngquant';
  }

  public function applyToImage($image_uri) {
    if ($cmd = $this->getFullPathToBinary()) {

      if ($this->getMimeType($image_uri) == 'image/png') {
        $dst = $this->sanitizeFilename($image_uri);
        $options = array(
          '--speed=1',
          '--quality=90-99',
          '--force',
          '--ext .png'
        );
        $arguments = array(
          $dst,
        );

        return $this->execShellCommand($cmd, $options, $arguments);
      }
    }
    return FALSE;
  }
}
