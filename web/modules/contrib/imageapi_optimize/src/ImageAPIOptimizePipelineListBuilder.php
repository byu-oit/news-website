<?php

namespace Drupal\imageapi_optimize;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of image optimize pipeline entities.
 *
 * @see \Drupal\imageapi_optimize\Entity\ImageAPIOptimizePipeline
 */
class ImageAPIOptimizePipelineListBuilder extends ConfigEntityListBuilder {

  /**
   * A form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('form_builder')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   */
  public function __construct(EntityTypeInterface $entity_type, ConfigEntityStorageInterface $storage, FormBuilderInterface $form_builder) {
    $this->entityTypeId = $entity_type->id();
    $this->storage = $storage;
    $this->entityType = $entity_type;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Pipeline name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $flush = [
      'title' => $this->t('Flush'),
      'weight' => 200,
      'url' => $entity->urlInfo('flush-form'),
    ];

    return parent::getDefaultOperations($entity) + [
      'flush' => $flush,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are currently no pipelines. <a href=":url">Add a new one</a>.', [
      ':url' => Url::fromRoute('imageapi_optimize.pipeline_add')->toString(),
    ]);
    $build['config_form'] = $this->formBuilder->getForm('Drupal\imageapi_optimize\Form\ImageAPIOptimizeDefaultPipelineConfigForm');
    return $build;
  }

}
