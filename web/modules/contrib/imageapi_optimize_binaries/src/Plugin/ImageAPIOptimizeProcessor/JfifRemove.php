<?php

namespace Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor;

use Drupal\imageapi_optimize_binaries\ImageAPIOptimizeProcessorBinaryBase;

/**
 * Uses the JfifRemove binary to optimize images.
 *
 * @ImageAPIOptimizeProcessor(
 *   id = "jfifremove",
 *   label = @Translation("JfifRemove"),
 *   description = @Translation("Uses the JfifRemove binary to optimize images.")
 * )
 */
class JfifRemove extends ImageAPIOptimizeProcessorBinaryBase {

  /**
   * {@inheritdoc}
   */
  protected function executableName() {
    return 'jfifremove';
  }

  public function applyToImage($image_uri) {
    if ($cmd = $this->getFullPathToBinary()) {

      if ($this->getMimeType($image_uri) == 'image/jpeg') {
        $dst = $this->sanitizeFilename($image_uri);
        $options = array();
        $arguments = array(
          $dst,
        );

        $option_string = implode(' ', $options);
        $argument_string = implode(' ', array_map('escapeshellarg', $arguments));
        return $this->saveCommandStdoutToFile(escapeshellarg($cmd) . ' ' . $option_string . ' < ' . $argument_string, $dst);
      }
    }
    return FALSE;
  }
}
