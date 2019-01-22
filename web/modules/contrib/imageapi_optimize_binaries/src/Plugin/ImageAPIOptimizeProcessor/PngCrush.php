<?php

namespace Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor;

use Drupal\imageapi_optimize_binaries\ImageAPIOptimizeProcessorBinaryBase;

/**
 * Uses the PngCrush binary to optimize images.
 *
 * @ImageAPIOptimizeProcessor(
 *   id = "pngcrush",
 *   label = @Translation("PngCrush"),
 *   description = @Translation("Uses the PngCrush binary to optimize images.")
 * )
 */
class PngCrush extends ImageAPIOptimizeProcessorBinaryBase {

  /**
   * {@inheritdoc}
   */
  protected function executableName() {
    return 'pngcrush';
  }

  public function applyToImage($image_uri) {
    if ($cmd = $this->getFullPathToBinary()) {
      $dst = $this->sanitizeFilename($image_uri);

      if ($this->getMimeType($image_uri) == 'image/png') {

        $temp_file = $this->fileSystem->tempnam('temporary://', 'file');
        $options = array(
          '-rem alla',
          '-reduce',
          '-brute',
          '-q'
        );
        $arguments = array(
          $dst,
          $this->fileSystem->realpath($temp_file),
        );

        if ($this->execShellCommand($cmd, $options, $arguments)) {
          return (bool) file_unmanaged_move($temp_file, $image_uri, FILE_EXISTS_REPLACE);
        }
      }
    }
    return FALSE;
  }
}
