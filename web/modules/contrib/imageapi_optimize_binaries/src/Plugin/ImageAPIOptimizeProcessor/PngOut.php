<?php

namespace Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor;

use Drupal\imageapi_optimize_binaries\ImageAPIOptimizeProcessorBinaryBase;

/**
 * Uses the PngOut binary to optimize images.
 *
 * @ImageAPIOptimizeProcessor(
 *   id = "pngout",
 *   label = @Translation("PngOut"),
 *   description = @Translation("Uses the PngOut binary to optimize images.")
 * )
 */
class PngOut extends ImageAPIOptimizeProcessorBinaryBase {

  /**
   * {@inheritdoc}
   */
  protected function executableName() {
    return 'pngout';
  }

  public function applyToImage($image_uri) {
    if ($cmd = $this->getFullPathToBinary()) {
      $dst = $this->sanitizeFilename($image_uri);

      if ($this->getMimeType($image_uri) == 'image/png') {
        $options = array(
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
