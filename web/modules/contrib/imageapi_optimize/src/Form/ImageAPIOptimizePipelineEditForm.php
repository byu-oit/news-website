<?php

namespace Drupal\imageapi_optimize\Form;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorInterface;
use Drupal\imageapi_optimize\ImageAPIOptimizeProcessorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for image optimize pipeline edit form.
 */
class ImageAPIOptimizePipelineEditForm extends ImageAPIOptimizePipelineFormBase {

  /**
   * The image optimize processor manager service.
   *
   * @var \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorManager
   */
  protected $imageAPIOptimizeProcessorManager;

  /**
   * Constructs an ImageAPIOptimizePipelineEditForm object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $imageapi_optimize_pipeline_storage
   *   The storage.
   * @param \Drupal\imageapi_optimize\ImageAPIOptimizeProcessorManager $imageapi_optimize_processor_manager
   *   The image optimize processor manager service.
   */
  public function __construct(EntityStorageInterface $imageapi_optimize_pipeline_storage, ImageAPIOptimizeProcessorManager $imageapi_optimize_processor_manager) {
    parent::__construct($imageapi_optimize_pipeline_storage);
    $this->imageAPIOptimizeProcessorManager = $imageapi_optimize_processor_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('imageapi_optimize_pipeline'),
      $container->get('plugin.manager.imageapi_optimize.processor')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $user_input = $form_state->getUserInput();
    $form['#title'] = $this->t('Edit pipeline %name', ['%name' => $this->entity->label()]);
    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'imageapi_optimize/admin';

    // Build the list of existing image processors for this image optimize pipeline.
    $form['processors'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Processor'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'image-processor-order-weight',
        ],
      ],
      '#attributes' => [
        'id' => 'image-pipeline-processors',
      ],
      '#empty' => t('There are currently no processors in this pipeline. Add one by selecting an option below.'),
      // Render processors below parent elements.
      '#weight' => 5,
    ];
    foreach ($this->entity->getProcessors() as $processor) {
      $key = $processor->getUuid();
      $form['processors'][$key]['#attributes']['class'][] = 'draggable';
      $form['processors'][$key]['#weight'] = isset($user_input['processors']) ? $user_input['processors'][$key]['weight'] : NULL;
      $form['processors'][$key]['processor'] = [
        '#tree' => FALSE,
        'data' => [
          'label' => [
            '#plain_text' => $processor->label(),
          ],
        ],
      ];

      $summary = $processor->getSummary();

      if (!empty($summary)) {
        $summary['#prefix'] = ' ';
        $form['processors'][$key]['processor']['data']['summary'] = $summary;
      }

      $form['processors'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $processor->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $processor->getWeight(),
        '#attributes' => [
          'class' => ['image-processor-order-weight'],
        ],
      ];

      $links = [];
      $is_configurable = $processor instanceof ConfigurableImageAPIOptimizeProcessorInterface;
      if ($is_configurable) {
        $links['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('imageapi_optimize.processor_edit_form', [
            'imageapi_optimize_pipeline' => $this->entity->id(),
            'imageapi_optimize_processor' => $key,
          ]),
        ];
      }
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('imageapi_optimize.processor_delete', [
          'imageapi_optimize_pipeline' => $this->entity->id(),
          'imageapi_optimize_processor' => $key,
        ]),
      ];
      $form['processors'][$key]['operations'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
    }

    // Build the new image processor addition form and add it to the processor list.
    $new_processor_options = [];
    $processors = $this->imageAPIOptimizeProcessorManager->getDefinitions();
    uasort($processors, function ($a, $b) {
      return strcasecmp($a['id'], $b['id']);
    });
    foreach ($processors as $processor => $definition) {
      $new_processor_options[$processor] = $definition['label'];
    }
    $form['processors']['new'] = [
      '#tree' => FALSE,
      '#weight' => isset($user_input['weight']) ? $user_input['weight'] : NULL,
      '#attributes' => ['class' => ['draggable']],
    ];
    $form['processors']['new']['processor'] = [
      'data' => [
        'new' => [
          '#type' => 'select',
          '#title' => $this->t('Processor'),
          '#title_display' => 'invisible',
          '#options' => $new_processor_options,
          '#empty_option' => $this->t('Select a new processor'),
        ],
        [
          'add' => [
            '#type' => 'submit',
            '#value' => $this->t('Add'),
            '#validate' => ['::processorValidate'],
            '#submit' => ['::submitForm', '::processorSave'],
          ],
        ],
      ],
      '#prefix' => '<div class="image-pipeline-new">',
      '#suffix' => '</div>',
    ];

    $form['processors']['new']['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight for new processor'),
      '#title_display' => 'invisible',
      '#default_value' => count($this->entity->getProcessors()) + 1,
      '#attributes' => ['class' => ['image-processor-order-weight']],
    ];
    $form['processors']['new']['operations'] = [
      'data' => [],
    ];

    return parent::form($form, $form_state);
  }

  /**
   * Validate handler for image optimize processor.
   */
  public function processorValidate($form, FormStateInterface $form_state) {
    if (!$form_state->getValue('new')) {
      $form_state->setErrorByName('new', $this->t('Select a processor to add.'));
    }
  }

  /**
   * Submit handler for image optimize processor.
   */
  public function processorSave($form, FormStateInterface $form_state) {
    $this->save($form, $form_state);

    // Check if this field has any configuration options.
    $processor = $this->imageAPIOptimizeProcessorManager->getDefinition($form_state->getValue('new'));

    // Load the configuration form for this option.
    if (is_subclass_of($processor['class'], '\Drupal\imageapi_optimize\ConfigurableImageAPIOptimizeProcessorInterface')) {
      $form_state->setRedirect(
        'imageapi_optimize.processor_add_form',
        [
          'imageapi_optimize_pipeline' => $this->entity->id(),
          'imageapi_optimize_processor' => $form_state->getValue('new'),
        ],
        ['query' => ['weight' => $form_state->getValue('weight')]]
      );
    }
    // If there's no form, immediately add the image processor.
    else {
      $processor = [
        'id' => $processor['id'],
        'data' => [],
        'weight' => $form_state->getValue('weight'),
      ];
      $processor_id = $this->entity->addProcessor($processor);
      $this->entity->save();
      if (!empty($processor_id)) {
        drupal_set_message($this->t('The Image Optimize processor was successfully applied.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Update image optimize processor weights.
    if (!$form_state->isValueEmpty('processors')) {
      $this->updateProcessorWeights($form_state->getValue('processors'));
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('Changes to the pipeline have been saved.'));
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update pipeline');

    return $actions;
  }

  /**
   * Updates image optimize processor weights.
   *
   * @param array $processors
   *   Associative array with processors having processor uuid as keys and array
   *   with processor data as values.
   */
  protected function updateProcessorWeights(array $processors) {
    foreach ($processors as $uuid => $processor_data) {
      if ($this->entity->getProcessors()->has($uuid)) {
        $this->entity->getProcessor($uuid)->setWeight($processor_data['weight']);
      }
    }
  }

}
