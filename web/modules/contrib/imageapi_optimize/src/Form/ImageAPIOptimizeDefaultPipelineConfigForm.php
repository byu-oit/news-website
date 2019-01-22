<?php

namespace Drupal\imageapi_optimize\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class ImageAPIOptimizeDefaultPipelineConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'imageapi_optimize_default_pipeline';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['imageapi_optimize.settings'];

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('imageapi_optimize.settings');
    $form['default_pipeline'] = [
      '#type' => 'select',
      '#title' => $this->t('Sitewide default pipeline'),
      '#description' => $this->t("When selecting a pipeline to use elsewhere you may simply select 'default' to use whatever pipeline you have set here."),
      '#options' => imageapi_optimize_pipeline_options(TRUE, FALSE),
      '#default_value' => $config->get('default_pipeline'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('imageapi_optimize.settings')
      ->set('default_pipeline', $values['default_pipeline'])
      ->save();
  }

}
