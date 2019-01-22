<?php

namespace Drupal\imageapi_optimize\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\imageapi_optimize\ImageAPIOptimizeProcessorPluginCollection;
use Drupal\imageapi_optimize\ImageAPIOptimizeProcessorInterface;
use Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface;

/**
 * Defines an image optimize pipeline configuration entity.
 *
 * @ConfigEntityType(
 *   id = "imageapi_optimize_pipeline",
 *   label = @Translation("Image Optimize Pipeline"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\imageapi_optimize\Form\ImageAPIOptimizePipelineAddForm",
 *       "edit" = "Drupal\imageapi_optimize\Form\ImageAPIOptimizePipelineEditForm",
 *       "delete" = "Drupal\imageapi_optimize\Form\ImageAPIOptimizePipelineDeleteForm",
 *       "flush" = "Drupal\imageapi_optimize\Form\ImageAPIOptimizePipelineFlushForm"
 *     },
 *     "list_builder" = "Drupal\imageapi_optimize\ImageAPIOptimizePipelineListBuilder",
 *     "storage" = "Drupal\imageapi_optimize\ImageAPIOptimizePipelineStorage",
 *   },
 *   admin_permission = "administer imageapi optimize pipelines",
 *   config_prefix = "pipeline",
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label"
 *   },
 *   links = {
 *     "flush-form" = "/admin/config/media/imageapi-optimize-pipelines/manage/{imageapi_optimize_pipeline}/flush",
 *     "edit-form" = "/admin/config/media/imageapi-optimize-pipelines/manage/{imageapi_optimize_pipeline}",
 *     "delete-form" = "/admin/config/media/imageapi-optimize-pipelines/manage/{imageapi_optimize_pipeline}/delete",
 *     "collection" = "/admin/config/media/imageapi-optimize-pipelines",
 *   },
 *   config_export = {
 *     "name",
 *     "label",
 *     "processors",
 *   }
 * )
 */
class ImageAPIOptimizePipeline extends ConfigEntityBase implements ImageAPIOptimizePipelineInterface, EntityWithPluginCollectionInterface {

  /**
   * The name of the image optimize pipeline.
   *
   * @var string
   */
  protected $name;

  /**
   * The image optimize pipeline label.
   *
   * @var string
   */
  protected $label;

  /**
   * The array of image optimize processors for this image optimize pipeline.
   *
   * @var array
   */
  protected $processors = [];

  /**
   * Holds the collection of image optimize processors that are used by this image optimize pipeline.
   *
   * @var \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorPluginCollection
   */
  protected $processorsCollection;

  /**
   * An array of temporary files that can be deleted on destruction.
   */
  protected $temporaryFiles = [];

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if ($update) {
      if (!empty($this->original) && $this->id() !== $this->original->id()) {
        // The old image optimize pipeline name needs flushing after a rename.
        $this->original->flush();
        // Update field settings if necessary.
        if (!$this->isSyncing()) {
          static::replaceImageAPIOptimizePipeline($this);
        }
      }
      else {
        // Flush pipeline when updating without changing the name.
        $this->flush();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    /** @var \Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface[] $entities */
    foreach ($entities as $pipeline) {
      // Flush cached media for the deleted pipeline.
      $pipeline->flush();
      // Clear the replacement ID, if one has been previously stored.
      /** @var \Drupal\imageapi_optimize\ImageAPIOptimizePipelineStorageInterface $storage */
      $storage->clearReplacementId($pipeline->id());
    }
  }

  /**
   * Update field settings if the image optimize pipeline name is changed.
   *
   * @param \Drupal\imageapi_optimize\ImageAPIOptimizePipelineInterface $pipeline
   *   The image optimize pipeline.
   */
  protected static function replaceImageAPIOptimizePipeline(ImageAPIOptimizePipelineInterface $pipeline) {
    if ($pipeline->id() != $pipeline->getOriginalId()) {
      // Loop through all image optimize pipelines looking for usages.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function flush() {

    // Get all image optimize pipelines and if they use this pipeline, flush it.
    $style_storage = $this->entityTypeManager()->getStorage('image_style');
    foreach ($style_storage->loadMultiple() as $style) {
      /** @var ImageStyleWithPipeline $style */
      if ($style->hasPipeline() && $style->getPipelineEntity()->id() == $this->id()) {
        $style->flush();
      }
    }

    // Let other modules update as necessary on flush.
    $module_handler = \Drupal::moduleHandler();
    $module_handler->invokeAll('imageapi_optimize_pipeline_flush', [$this]);

    Cache::invalidateTags($this->getCacheTagsToInvalidate());

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function applyToImage($image_uri) {

    // If the source file doesn't exist, return FALSE.
    $image = \Drupal::service('image.factory')->get($image_uri);
    if (!$image->isValid()) {
      return FALSE;
    }

    if (count($this->getProcessors())) {
      /*
      Copy image to optimize to a temp location so that:
      1. It's always a local image.
      2. The filename is only ascii characters.
      3. Multiple pipelines can be applied to the same image at the same time.
      */
      $file_extension = substr(strrchr($image_uri,'.'),1);
      $temp_image_uri_base = \Drupal::service('file_system')->tempnam('temporary://', 'image_api_optimize_' . $this->id());
      // Ensure this file is cleaned up at the end of the request.
      $this->temporaryFiles[] = $temp_image_uri_base;
      // Copy the file we are trying to optimize to the temporary location.
      file_unmanaged_copy($image_uri, $temp_image_uri_base, FILE_EXISTS_REPLACE);

      // Some processors require the correct file extension, add that on.
      $temp_image_uri = $temp_image_uri_base . '.' .$file_extension;
      file_unmanaged_move($temp_image_uri_base, $temp_image_uri);
      // Ensure this file is cleaned up at the end of the request.
      $this->temporaryFiles[] = $temp_image_uri;
      $image_changed = FALSE;

      foreach ($this->getProcessors() as $processor) {
        $image_changed = $processor->applyToImage($temp_image_uri) || $image_changed;
        // The file may have changed on disk after each processor has been
        // applied, and PHP has a cache of file size information etc. so clear
        // it here so that later calls to filesize() etc. get the correct
        // information.
        clearstatcache();
      }

      if ($image_changed) {
        // Copy the temporary file back over the original image.
        file_unmanaged_move($temp_image_uri, $image_uri, FILE_EXISTS_REPLACE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteProcessor(ImageAPIOptimizeProcessorInterface $processor) {
    $this->getProcessors()->removeInstanceId($processor->getUuid());
    $this->save();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessor($processor) {
    return $this->getProcessors()->get($processor);
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessors() {
    if (!$this->processorsCollection) {
      $this->processorsCollection = $this->getProcessorsCollection();
      $this->processorsCollection->sort();
    }
    return $this->processorsCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessorsCollection() {
    return new ImageAPIOptimizeProcessorPluginCollection($this->getImageAPIOptimizeProcessorPluginManager(), $this->processors);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return ['processors' => $this->getProcessors()];
  }

  /**
   * {@inheritdoc}
   */
  public function addProcessor(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getProcessors()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getReplacementID() {
    /** @var \Drupal\imageapi_optimize\ImageAPIOptimizePipelineStorageInterface $storage */
    $storage = $this->entityTypeManager()->getStorage($this->getEntityTypeId());
    return $storage->getReplacementId($this->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name');
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * Returns the image optimize processor plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The image optimize processor plugin manager.
   */
  protected function getImageAPIOptimizeProcessorPluginManager() {
    return \Drupal::service('plugin.manager.imageapi_optimize.processor');
  }

  /**
   * Clean up any temporary files created in optimization.
   */
  public function __destruct() {
    foreach ($this->temporaryFiles as $file) {
      if (file_exists($file)) {
        file_unmanaged_delete($file);
      }
    }
  }

}
