<?php

namespace Drupal\imageapi_optimize;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Hook implementations for the Image Optimize module.
 */
class ImageAPIOptimizeHookImplementations {

  use StringTranslationTrait;

  /**
   * Constructs a new ImageAPIOptimizeHookImplementations object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The stream translation service.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * Implements hook_entity_type_alter().
   */
  public function entity_type_alter(array &$entity_types) {
    /** @var $entity_types \Drupal\Core\Entity\EntityTypeInterface[] */
    if (isset($entity_types['image_style'])) {
      $image_style = $entity_types['image_style'];
      $image_style->setClass('Drupal\imageapi_optimize\Entity\ImageStyleWithPipeline');
      $image_style->setHandlerClass('list_builder', 'Drupal\imageapi_optimize\ImageStyleWithPipelineListBuilder');
      $config_export = $image_style->get('config_export');
      $config_export[] = 'pipeline';
      $image_style->set('config_export', $config_export);
    }
  }

  /**
   * Implements hook_form_image_style_edit_form_alter().
   */
  public function form_image_style_edit_form_alter(&$form, FormStateInterface $form_state, $form_id) {
    $entity = $form_state->getFormObject()->getEntity();
    $form['pipeline'] = [
      '#type' => 'select',
      '#title' => $this->t('Image Optimize Pipeline'),
      '#options' => imageapi_optimize_pipeline_options(),
      '#default_value' => $entity->getPipeline(),
      '#description' => $this->t('Optionally select an Image Optimization pipeline which will be applied after all effects in this image style.'),
      '#weight' => 10,
    ];
  }

  /**
   * Implements hook_config_schema_info_alter().
   */
  public function config_schema_info_alter(&$definitions) {
    if (isset($definitions['image.style.*'])) {
      $definitions['image.style.*']['mapping']['pipeline']['type'] = 'string';
    }
  }

}
