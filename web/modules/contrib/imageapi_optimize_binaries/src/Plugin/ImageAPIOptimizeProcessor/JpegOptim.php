<?php

namespace Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\imageapi_optimize_binaries\ImageAPIOptimizeProcessorBinaryBase;

/**
 * Uses the JpegOptim binary to optimize images.
 *
 * @ImageAPIOptimizeProcessor(
 *   id = "jpegoptim",
 *   label = @Translation("JpegOptim"),
 *   description = @Translation("Uses the JpegOptim binary to optimize images.")
 * )
 */
class JpegOptim extends ImageAPIOptimizeProcessorBinaryBase {

  /**
   * {@inheritdoc}
   */
  protected function executableName() {
    return 'jpegoptim';
  }

  public function applyToImage($image_uri) {
    if ($cmd = $this->getFullPathToBinary()) {
      $dst = $this->sanitizeFilename($image_uri);

      if ($this->getMimeType($image_uri) == 'image/jpeg') {
        $options = array(
          '--quiet',
          '--strip-all',
        );
        $arguments = array(
          $dst,
        );

        if (is_numeric($this->configuration['progressive'])) {
          switch ($this->configuration['progressive']) {
            case 0:
              $options[] = '--all-normal';
              break;

            case 1:
              $options[] = '--all-progressive';
              break;
          }
        }

        if (is_numeric($this->configuration['quality'])) {
          $options[] = '--max=' . escapeshellarg($this->configuration['quality']);
        }

        if (is_numeric($this->configuration['size'])) {
          $options[] = '--size=' . escapeshellarg($this->configuration['size'] . '%');
        }

        return $this->execShellCommand($cmd, $options, $arguments);
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'progressive' => '',
      'quality' => '',
      'size' => '',
    ];
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['progressive'] = array(
      '#title' => $this->t('Progressive'),
      '#type' => 'select',
      '#options' => array(
        '' => $this->t('No change'),
        0 => $this->t('Non-progressive'),
        1 => $this->t('Progressive'),
      ),
      '#default_value' => $this->configuration['progressive'],
      '#description' => $this->t('If "No change" is selected, the output will have the same as the input.'),
    );

    $form['quality'] = array(
      '#title' => $this->t('Quality'),
      '#type' => 'number',
      '#min' => 0,
      '#max' => 100,
      '#description' => $this->t('Optionally enter a JPEG quality setting to use, 0 - 100. WARNING: LOSSY'),
      '#default_value' => $this->configuration['quality'],
    );

    $form['size'] = array(
      '#title' => $this->t('Target size'),
      '#type' => 'number',
      '#min' => 1,
      '#max' => 100,
      '#field_suffix' => '%',
      '#description' => $this->t('Optionally enter a target percentage of filesize for optimisation. WARNING: LOSSY'),
      '#default_value' => $this->configuration['size'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['progressive'] = $form_state->getValue('progressive');
    $this->configuration['quality'] = $form_state->getValue('quality');
    $this->configuration['size'] = $form_state->getValue('size');
  }
}
