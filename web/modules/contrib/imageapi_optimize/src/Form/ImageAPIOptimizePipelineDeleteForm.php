<?php

namespace Drupal\imageapi_optimize\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Creates a form to delete an image optimize pipeline.
 */
class ImageAPIOptimizePipelineDeleteForm extends EntityDeleteForm {

  /**
   * Replacement options.
   *
   * @var array
   */
  protected $replacementOptions;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Optionally select a pipeline before deleting %pipeline', ['%pipeline' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    if (count($this->getReplacementOptions()) > 1) {
      return $this->t('If this pipeline is in use on the site, you may select another pipeline to replace it. If no replacement pipeline is selected, the dependent configurations might need manual reconfiguration.');
    }
    return $this->t('The dependent configurations might need manual reconfiguration.');
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $replacement_pipelines = $this->getReplacementOptions();
    // If there are non-empty options in the list, allow the user to optionally
    // pick up a replacement.
    if (count($replacement_pipelines) > 1) {
      $form['replacement'] = [
        '#type' => 'select',
        '#title' => $this->t('Replacement pipeline'),
        '#options' => $replacement_pipelines,
        '#empty_option' => $this->t('- No replacement -'),
        '#weight' => -5,
      ];
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save a selected replacement in the image optimize pipeline storage. It will be used
    // later, in the same request, when resolving dependencies.
    if ($replacement = $form_state->getValue('replacement')) {
      /** @var \Drupal\imageapi_optimize\ImageAPIOptimizePipelineStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage($this->entity->getEntityTypeId());
      $storage->setReplacementId($this->entity->id(), $replacement);
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * Returns a list of image optimize pipeline replacement options.
   *
   * @return array
   *   An option list suitable for the form select '#options'.
   */
  protected function getReplacementOptions() {
    if (!isset($this->replacementOptions)) {
      $this->replacementOptions = array_diff_key(imageapi_optimize_pipeline_options(), [$this->getEntity()->id() => '']);
    }
    return $this->replacementOptions;
  }

}
