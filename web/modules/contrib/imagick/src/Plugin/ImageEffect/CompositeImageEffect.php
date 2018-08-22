<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Drupal\imagick\ImagickConst;
use Imagick;

/**
 * Composite one image onto another.
 *
 * @ImageEffect(
 *   id = "image_composite",
 *   label = @Translation("Composite"),
 *   description = @Translation("Composite one image onto another at the specified offset.")
 * )
 */
class CompositeImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('composite', $this->configuration)) {
      $this->logger->error('Image composite failed using the %toolkit toolkit on %path (%mimetype)', [
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
  public function getSummary() {
    $summary = [
      '#markup' => $this->configuration['path'],
      '#effect' => [
        'id' => $this->pluginDefinition['id'],
        'label' => $this->label(),
        'description' => $this->pluginDefinition['description'],
      ],
    ];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'path' => '',
      'composite' => Imagick::COMPOSITE_DEFAULT,
      'x' => '0',
      'y' => '0',
      'channel' => [Imagick::CHANNEL_DEFAULT],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#description' => $this->t('Path to the composite image. In- or external URL\'s are possible'),
      '#default_value' => $this->configuration['path'],
    ];
    $form['composite'] = [
      '#type' => 'select',
      '#title' => $this->t('Composite'),
      '#options' => ImagickConst::composites(),
      '#description' => $this->t('Composite operator'),
      '#default_value' => $this->configuration['composite'],
    ];
    $form['x'] = [
      '#type' => 'number',
      '#title' => $this->t('X-offset'),
      '#description' => $this->t('The column offset of the composited image'),
      '#default_value' => $this->configuration['x'],
    ];
    $form['y'] = [
      '#type' => 'number',
      '#title' => $this->t('Y-offset'),
      '#description' => $this->t('The row offset of the composited image'),
      '#default_value' => $this->configuration['y'],
    ];
    $form['channel'] = [
      '#type' => 'select',
      '#title' => $this->t('Channel'),
      '#options' => ImagickConst::channels(),
      '#multiple' => TRUE,
      '#size' => 10,
      '#description' => $this->t('Provide any channel constant that is valid for your channel mode. It is possible to apply more than one channel'),
      '#default_value' => $this->configuration['channel'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['path'] = $form_state->getValue('path');
    $this->configuration['composite'] = $form_state->getValue('composite');
    $this->configuration['x'] = $form_state->getValue('x');
    $this->configuration['y'] = $form_state->getValue('y');
    $this->configuration['channel'] = $form_state->getValue('channel');
  }

}