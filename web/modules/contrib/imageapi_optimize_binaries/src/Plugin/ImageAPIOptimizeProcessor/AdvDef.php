<?php

namespace Drupal\imageapi_optimize_binaries\Plugin\ImageAPIOptimizeProcessor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\imageapi_optimize_binaries\ImageAPIOptimizeProcessorBinaryBase;

/**
 * Uses the AdvDef binary to optimize images.
 *
 * @ImageAPIOptimizeProcessor(
 *   id = "advdef",
 *   label = @Translation("AdvDef"),
 *   description = @Translation("Uses the AdvDef binary to optimize images.")
 * )
 */
class AdvDef extends ImageAPIOptimizeProcessorBinaryBase {

  /**
   * {@inheritdoc}
   */
  protected function executableName() {
    return 'advdef';
  }

  public function applyToImage($image_uri) {
    if ($cmd = $this->getFullPathToBinary()) {

      if ($this->getMimeType($image_uri) == 'image/png') {
        $options = array(
          '--quiet',
        );
        $arguments = array(
          $this->sanitizeFilename($image_uri),
        );

        if ($this->configuration['recompress']) {
          $options[] = '--recompress';
        }

        $options[] = '-' . $this->configuration['mode'];

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
      'recompress' => TRUE,
      'mode' => 3,
    ];
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['recompress'] = array(
      '#title' => $this->t('Recompress'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['recompress'],
    );

    $form['mode'] = array(
      '#title' => $this->t('Compression mode'),
      '#type' => 'select',
      '#options' => array(
        0 => $this->t('Disabled'),
        1 => $this->t('Fast'),
        2 => $this->t('Normal'),
        3 => $this->t('Extra'),
        4 => $this->t('Insane'),
      ),
      '#default_value' => $this->configuration['mode'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['recompress'] = $form_state->getValue('recompress');
    $this->configuration['mode'] = $form_state->getValue('mode');
  }
}
