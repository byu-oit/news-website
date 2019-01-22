<?php

namespace Drupal\imageapi_optimize\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for image optimize pipeline add and edit forms.
 */
abstract class ImageAPIOptimizePipelineFormBase extends EntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface
   */
  protected $entity;

  /**
   * The image optimize pipeline entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $imageapiOptimizePipelineStorage;

  /**
   * Constructs a base class for image optimize pipeline add and edit forms.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $imageapi_optimize_pipeline_storage
   *   The image optimize pipeline entity storage.
   */
  public function __construct(EntityStorageInterface $imageapi_optimize_pipeline_storage) {
    $this->imageapiOptimizePipelineStorage = $imageapi_optimize_pipeline_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('imageapi_optimize_pipeline')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image optimize pipeline name'),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];
    $form['name'] = [
      '#type' => 'machine_name',
      '#machine_name' => [
        'exists' => [$this->imageapiOptimizePipelineStorage, 'load'],
      ],
      '#default_value' => $this->entity->id(),
      '#required' => TRUE,
    ];

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->urlInfo('edit-form'));
  }

}
