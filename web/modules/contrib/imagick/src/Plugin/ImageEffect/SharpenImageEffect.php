<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Applies the sharpen effect on an image resource.
 *
 * @ImageEffect(
 *   id = "image_sharpen",
 *   label = @Translation("Sharpen"),
 *   description = @Translation("Applies the sharpen effect on an image.")
 * )
 */
class SharpenImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('sharpen', $this->configuration)) {
      $this->logger->error('Image sharpen failed using the %toolkit toolkit on %path (%mimetype)', [
        '%toolkit' => $image->getToolkitId(),
        '%path' => $image->getSource(),
        '%mimetype' => $image->getMimeType()
      ]);
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'radius' => '0',
      'sigma' => '1',
      'amount' => '1.0',
      'threshold' => '0.05',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['radius'] = [
      '#type' => 'number',
      '#title' => $this->t('Radius'),
      '#description' => $this->t('The radius of the Gaussian, in pixels, not counting the center pixel. Use 0 for auto-select.'),
      '#default_value' => $this->configuration['radius'],
    ];
    $form['sigma'] = [
      '#type' => 'number',
      '#title' => $this->t('Sigma'),
      '#description' => $this->t('The standard deviation of the Gaussian, in pixels.'),
      '#default_value' => $this->configuration['sigma'],
    ];
    $form['amount'] = [
      '#type' => 'number',
      '#step' => '0.1',
      '#title' => t('Amount'),
      '#description' => t('The fraction of the difference between the original and the blur image that is added back into the original.'),
      '#default_value' => $this->configuration['amount'],
    ];
    $form['threshold'] = [
      '#type' => 'number',
      '#step' => '0.01',
      '#title' => t('Threshold'),
      '#description' => t('The threshold, as a fraction of QuantumRange, needed to apply the difference amount.'),
      '#default_value' => $this->configuration['threshold'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['radius'] = $form_state->getValue('radius');
    $this->configuration['sigma'] = $form_state->getValue('sigma');
    $this->configuration['amount'] = $form_state->getValue('amount');
    $this->configuration['threshold'] = $form_state->getValue('threshold');
  }

}
