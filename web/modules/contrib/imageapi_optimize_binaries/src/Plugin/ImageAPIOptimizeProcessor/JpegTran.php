<?php

namespace Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\imageapi_optimize_binaries\ImageAPIOptimizeProcessorBinaryBase;

/**
 * Uses the JpegTran binary to optimize images.
 *
 * @ImageAPIOptimizeProcessor(
 *   id = "jpegtran",
 *   label = @Translation("JpegTran"),
 *   description = @Translation("Uses the JpegTran binary to optimize images.")
 * )
 */
class JpegTran extends ImageAPIOptimizeProcessorBinaryBase {

  /**
   * {@inheritdoc}
   */
  protected function executableName() {
    return 'jpegtran';
  }

  public function applyToImage($image_uri) {
    if ($cmd = $this->getFullPathToBinary()) {
      $dst = $this->sanitizeFilename($image_uri);

      if ($this->getMimeType($image_uri) == 'image/jpeg') {
        $options = array(
          '-copy none',
          '-optimize',
        );
        $arguments = array(
          $dst,
        );

        if ($this->configuration['progressive']) {
          $options[] = '-progressive';
        }

        $option_string = implode(' ', $options);
        $argument_string = implode(' ', array_map('escapeshellarg', $arguments));
        return $this->saveCommandStdoutToFile(escapeshellarg($cmd) . ' ' . $option_string . ' ' . $argument_string, $dst);
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'progressive' => FALSE,
    ];
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['progressive'] = array(
      '#title' => $this->t('Progressive'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['progressive'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['progressive'] = $form_state->getValue('progressive');
  }
}
