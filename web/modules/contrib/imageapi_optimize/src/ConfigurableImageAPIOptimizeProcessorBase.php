<?php

namespace Drupal\imageapi_optimize;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a base class for configurable image optimize processors.
 *
 * @see \Drupal\imageapi_optimize\Annotation\ImageAPIOptimizeProcessor
 * @see \Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorInterface
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorBase
 * @see \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorManager
 * @see plugin_api
 */
abstract class ConfigurableImageAPIOptimizeProcessorBase extends ImageAPIOptimizeProcessorBase implements ConfigurableImageAPIOptimizeProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
