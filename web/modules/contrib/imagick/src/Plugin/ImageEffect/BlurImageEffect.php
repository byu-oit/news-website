<?php

namespace Drupal\imagick\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Drupal\imagick\ImagickConst;

/**
 * Blurs an image resource.
 *
 * @ImageEffect(
 *   id = "image_blur",
 *   label = @Translation("Blur"),
 *   description = @Translation("Blurs an image, different methods can be used.")
 * )
 */
class BlurImageEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    if (!$image->apply('blur', $this->configuration)) {
      $this->logger->error('Image blur failed using the %toolkit toolkit on %path (%mimetype)', [
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
      'type' => ImagickConst::NORMAL_BLUR,
      'radius' => '16',
      'sigma' => '16',
      'angle' => '0',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['help'] = ['#value' => $this->t('The intensity of the blur effect. For reasonable results, the radius should be larger than sigma.')];
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Blur type'),
      '#options' => [
        ImagickConst::NORMAL_BLUR => $this->t('Normal'),
        ImagickConst::ADAPTIVE_BLUR => $this->t('Adaptive'),
        ImagickConst::GAUSSIAN_BLUR => $this->t('Gaussian'),
        ImagickConst::MOTION_BLUR => $this->t('Motion'),
        ImagickConst::RADIAL_BLUR => $this->t('Radial'),
      ],
      '#default_value' => $this->configuration['type'],
    ];
    $form['radius'] = [
      '#type' => 'number',
      '#title' => $this->t('Radius'),
      '#description' => $this->t('The radius of the Gaussian, in pixels, not counting the center pixel.'),
      '#states' => [
        'invisible' => [
          ':input[name="data[type]"]' => [
            'value' => ImagickConst::RADIAL_BLUR,
          ],
        ],
      ],
      '#default_value' => $this->configuration['radius'],
    ];
    $form['sigma'] = [
      '#type' => 'number',
      '#title' => $this->t('Sigma'),
      '#description' => $this->t('The standard deviation of the Gaussian, in pixels'),
      '#states' => [
        'invisible' => [
          ':input[name="data[type]"]' => [
            'value' => ImagickConst::RADIAL_BLUR,
          ],
        ],
      ],
      '#default_value' => $this->configuration['sigma'],
    ];
    $form['angle'] = [
      '#type' => 'number',
      '#title' => $this->t('Angle'),
      '#description' => $this->t('The angle of the blur'),
      '#states' => [
        'visible' => [
          ':input[name="data[type]"]' => [
            ['value' => ImagickConst::MOTION_BLUR],
            ['value' => ImagickConst::RADIAL_BLUR],
          ],
        ],
      ],
      '#default_value' => $this->configuration['angle'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['type'] = $form_state->getValue('type');
    $this->configuration['radius'] = $form_state->getValue('radius');
    $this->configuration['sigma'] = $form_state->getValue('sigma');
    $this->configuration['angle'] = $form_state->getValue('angle');
  }

}
