<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Imagick;

/**
 * Blurs an image resource.
 *
 * @ImageEffect(
 *   id = "image_convert",
 *   label = @Translation("Convert"),
 *   description = @Translation("Converts image's filetype and quality")
 * )
 */
class ConvertImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('convert', $this->configuration)) {
      $this->logger->error('Image convert failed using the %toolkit toolkit on %path (%mimetype)', [
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
      'format' => 'JPG',
      'quality' => \Drupal::config('imagick.config')->get('jpeg_quality'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Load all available formats
    $formats = Imagick::queryFormats();

    $form['format'] = [
      '#title' => $this->t("File format"),
      '#type' => 'select',
      '#default_value' => $this->configuration['format'],
      '#options' => array_combine($formats, $formats),
    ];
    $form['quality'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Quality'),
      '#description' => $this->t('Override the default image quality. Higher values mean better image quality but bigger files.'),
      '#size' => 3,
      '#maxlength' => 3,
      '#default_value' => $this->configuration['quality'],
      '#field_suffix' => '%',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['format'] = $form_state->getValue('format');
    $this->configuration['quality'] = $form_state->getValue('quality');
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeExtension($extension) {
    return strtolower($this->configuration['format']);
  }

}
