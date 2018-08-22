<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Applies the convolve effect on an image resource.
 *
 * @ImageEffect(
 *   id = "image_convolve",
 *   label = @Translation("Convolve"),
 *   description = @Translation("Applies the convolve effect on an image.")
 * )
 */
class ConvolveImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('convolve', $this->configuration)) {
      $this->logger->error('Image convolve failed using the %toolkit toolkit on %path (%mimetype)', [
        '%toolkit' => $image->getToolkitId(),
        '%path' => $image->getSource(),
        '%mimetype' => $image->getMimeType(),
      ]);
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summary = parent::getSummary();
    $summary['#markup'] = '- ' . $this->configuration['label'];

    return $summary;
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
    if (isset($this->configuration['matrix'])) {
      $matrix_values = $this->configuration['matrix']['entries'];
    }
    else {
      $matrix_values = array_fill(0, 3, array_fill(0, 3, 1));
    }

    // kernel matrix inputs
    $form['matrix'] = [
      '#type' => 'item',
      '#title' => t('Kernel matrix'),
      '#collapset' => FALSE,
      '#required' => TRUE,
      '#prefix' => '<div class="matrix-wrapper">',
      '#suffix' => '</div>',
      '#attached' => [
        'library' => ['imagick/matrix'],
      ],
    ];
    $form['matrix']['entries'] = [];
    for ($i = 0; $i < 3; $i++) {
      $form['matrix']['entries'][$i] = [
        '#type' => 'fieldset',
      ];
      for ($j = 0; $j < 3; $j++) {
        $form['matrix']['entries'][$i][$j] = [
          '#type' => 'number',
          '#title' => t('Matrix entry') . " ($i,$j)",
          '#title_display' => 'invisible',
          '#default_value' => $matrix_values[$i][$j],
          '#required' => TRUE,
          '#element_validate' => [[$this, 'elementValidateNumber']],
        ];
      }
    }

    // filter label input
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#description' => t('A label to identify this filter effect.'),
      '#default_value' => $this->configuration['label'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['matrix'] = $form_state->getValue('matrix');
    $this->configuration['label'] = $form_state->getValue('label');
  }

}
