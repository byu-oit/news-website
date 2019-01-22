<?php

namespace Drupal\imageapi_optimize\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface;

/**
 * Form for deleting an image optimize processor.
 */
class ImageAPIOptimizeProcessorDeleteForm extends ConfirmFormBase {

  /**
   * The image optimize pipeline containing the image optimize processor to be deleted.
   *
   * @var \Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface
   */
  protected $imageAPIOptimizePipeline;

  /**
   * The image optimize processor to be deleted.
   *
   * @var \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface
   */
  protected $imageAPIOptimizeProcessor;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the @processor processor from the %pipeline pipeline?', ['%pipeline' => $this->imageAPIOptimizePipeline->label(), '@processor' => $this->imageAPIOptimizeProcessor->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->imageAPIOptimizePipeline->urlInfo('edit-form');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'imageapi_optimize_processor_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ImageAPIOptimizePipelineInterface $imageapi_optimize_pipeline = NULL, $imageapi_optimize_processor = NULL) {
    $this->imageAPIOptimizePipeline = $imageapi_optimize_pipeline;
    $this->imageAPIOptimizeProcessor = $this->imageAPIOptimizePipeline->getProcessor($imageapi_optimize_processor);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->imageAPIOptimizePipeline->deleteProcessor($this->imageAPIOptimizeProcessor);
    drupal_set_message($this->t('The Image Optimize processor %name has been deleted.', ['%name' => $this->imageAPIOptimizeProcessor->label()]));
    $form_state->setRedirectUrl($this->imageAPIOptimizePipeline->urlInfo('edit-form'));
  }

}
