<?php

namespace Drupal\imageapi_optimize\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface;

/**
 * Provides an edit form for image optimize processors.
 */
class ImageAPIOptimizeProcessorEditForm extends ImageAPIOptimizeProcessorFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ImageAPIOptimizePipelineInterface $imageapi_optimize_pipeline = NULL, $imageapi_optimize_processor = NULL) {
    $form = parent::buildForm($form, $form_state, $imageapi_optimize_pipeline, $imageapi_optimize_processor);

    $form['#title'] = $this->t('Edit %label processor', ['%label' => $this->imageAPIOptimizeProcessor->label()]);
    $form['actions']['submit']['#value'] = $this->t('Update processor');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareImageAPIOptimizeProcessor($imageapi_optimize_processor) {
    return $this->imageAPIOptimizePipeline->getProcessor($imageapi_optimize_processor);
  }

}
