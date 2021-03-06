<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;

/**
 * Applies the coloroverlay effect on an image resource.
 *
 * @ImageEffect(
 *   id = "image_coloroverlay",
 *   label = @Translation("Coloroverlay"),
 *   description = @Translation("Applies the coloroverlay effect on an image.")
 * )
 */
class ColoroverlayImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('coloroverlay', $this->configuration)) {
      $this->logger->error('Image coloroverlay failed using the %toolkit toolkit on %path (%mimetype)', [
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
      'HEX' => '#E2DB6A',
      'alpha' => 50,
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
    $form['alpha'] = [
      '#type' => 'number',
      '#title' => $this->t('Opacity'),
      '#description' => $this->t('Opacity of the color overlay in percents.'),
      '#default_value' => $this->configuration['alpha'],
      '#min' => 0,
      '#max' => 100,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['HEX'] = $form_state->getValue('HEX');
    $this->configuration['alpha'] = $form_state->getValue('alpha');
  }

}
