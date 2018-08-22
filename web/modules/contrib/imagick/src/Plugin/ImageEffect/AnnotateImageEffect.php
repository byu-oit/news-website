<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Imagick;

/**
 * Annotates an image resource.
 *
 * @ImageEffect(
 *   id = "image_annotate",
 *   label = @Translation("Annotate"),
 *   description = @Translation("Annotates an image resource.")
 * )
 */
class AnnotateImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('annotate', $this->configuration)) {
      $this->logger->error('Image annotate failed using the %toolkit toolkit on %path (%mimetype)', [
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
      'text_fieldset' => [
        'text' => 'Annotation',
        'font' => 'Helvetica',
        'size' => 20,
        'HEX' => '#000000',
      ],
      'position_fieldset' => [
        'anchor' => 'right-bottom',
        'padding_x' => 20,
        'padding_y' => 20,
      ],
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

    // Get fonts
    $imagick = new Imagick();
    $available_fonts = $imagick->queryFonts();

    $form['text_fieldset'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Text'),
      'text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Text'),
        '#description' => $this->t('Text to annotate the image with.'),
        '#default_value' => $this->configuration['text_fieldset']['text'],
      ],
      'font' => [
        '#type' => 'select',
        '#options' => array_combine($available_fonts, $available_fonts),
        '#title' => $this->t('Font'),
        '#description' => $this->t('Fonts that ImageMagick knows about.'),
        '#default_value' => $this->configuration['text_fieldset']['font'],
      ],
      'size' => [
        '#type' => 'textfield',
        '#title' => $this->t('Font size'),
        '#default_value' => $this->configuration['text_fieldset']['size'],
        '#size' => 3,
      ],
    ];
    $form['text_fieldset']['HEX'] = [
      '#type' => 'textfield',
      '#title' => $this->t('HEX'),
      '#default_value' => $this->configuration['text_fieldset']['HEX'],
      '#attributes' => [
        'class' => ['colorentry'],
      ],
    ];
    $form['text_fieldset']['colorpicker'] = [
      '#weight' => -1,
      '#type' => 'container',
      '#attributes' => [
        'class' => ['colorpicker'],
        'style' => ['float:right'],
      ],
    ];

    // Add Farbtastic color picker.
    $form['text_fieldset']['matte_color']['#attached'] = [
      'library' => ['imagick/colorpicker'],
    ];
    $form['position_fieldset'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Position'),
      'anchor' => [
        '#type' => 'radios',
        '#title' => $this->t('Anchor'),
        '#options' => [
          'left-top' => $this->t('Top left'),
          'center-top' => $this->t('Top center'),
          'right-top' => $this->t('Top right'),
          'left-center' => $this->t('Center left'),
          'center-center' => $this->t('Center'),
          'right-center' => $this->t('Center right'),
          'left-bottom' => $this->t('Bottom left'),
          'center-bottom' => $this->t('Bottom center'),
          'right-bottom' => $this->t('Bottom right'),
        ],
        '#theme' => 'image_anchor',
        '#default_value' => $this->configuration['position_fieldset']['anchor'],
      ],
      'padding_x' => [
        '#type' => 'textfield',
        '#title' => $this->t('Padding X'),
        '#default_value' => $this->configuration['position_fieldset']['padding_x'],
        '#description' => $this->t('Enter a value in pixels or percent'),
        '#size' => 3,
      ],
      'padding_y' => [
        '#type' => 'textfield',
        '#title' => $this->t('Padding Y'),
        '#default_value' => $this->configuration['position_fieldset']['padding_y'],
        '#description' => $this->t('Enter a value in pixels or percent'),
        '#size' => 3,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['text_fieldset']['text'] = $form_state->getValue([
      'text_fieldset',
      'text'
    ]);
    $this->configuration['text_fieldset']['font'] = $form_state->getValue([
      'text_fieldset',
      'font'
    ]);
    $this->configuration['text_fieldset']['size'] = $form_state->getValue([
      'text_fieldset',
      'size'
    ]);
    $this->configuration['text_fieldset']['HEX'] = $form_state->getValue([
      'text_fieldset',
      'HEX'
    ]);

    $this->configuration['position_fieldset']['anchor'] = $form_state->getValue([
      'position_fieldset',
      'anchor'
    ]);
    $this->configuration['position_fieldset']['padding_x'] = $form_state->getValue([
      'position_fieldset',
      'padding_x'
    ]);
    $this->configuration['position_fieldset']['padding_y'] = $form_state->getValue([
      'position_fieldset',
      'padding_y'
    ]);
  }

}
