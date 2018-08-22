<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Applies a colorshift effect on an image resource.
 *
 * @ImageEffect(
 *   id = "image_colorshift",
 *   label = @Translation("Colorshift"),
 *   description = @Translation("Applies a colorshift effect on an image.")
 * )
 */
class ColorshiftImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('colorshift', $this->configuration)) {
      $this->logger->error('Image colorshift failed using the %toolkit toolkit on %path (%mimetype)', [
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
      'HEX' => '#FF2E2E',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['colorform'],
      ],
    ];

    $form['HEX'] = [
      '#type' => 'textfield',
      '#title' => $this->t('HEX'),
      '#default_value' => $this->configuration['HEX'],
      '#attributes' => [
        'class' => ['colorentry'],
      ],
    ];
    $form['colorpicker'] = [
      '#weight' => -1,
      '#type' => 'container',
      '#attributes' => [
        'class' => ['colorpicker'],
        'style' => ['float:right'],
      ],
    ];

    // Add Farbtastic color picker.
    $form['matte_color']['#attached'] = [
      'library' => ['imagick/colorpicker'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['HEX'] = $form_state->getValue('HEX');
  }

}
