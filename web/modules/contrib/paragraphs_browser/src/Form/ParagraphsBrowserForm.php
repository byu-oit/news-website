<?php

/**
 * @file
 * Contains \Drupal\paragraphs_browser\Form\ParagraphsBrowserForm.
 */

namespace Drupal\paragraphs_browser\Form;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\paragraphs_browser\Ajax\AddParagraphTypeCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CleanupUrlAliases.
 *
 * @package Drupal\paragraphs_browser\Form
 */
class ParagraphsBrowserForm extends FormBase {

  /**
   * The index for which the fields are configured.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $entity;

  /**
   * The query factory to create entity queries.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a new ParagraphsTypeDeleteConfirm object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   */
  public function __construct(QueryFactory $query_factory, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->queryFactory = $query_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraphs_browser_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, FieldConfig $field_config = null, $paragraphs_browser_type = null, $uuid = null) {
    $form_state->addBuildInfo('uuid', $uuid);

    $form['#attached']['library'][] = 'paragraphs_browser/modal';

    $field_name = $field_config->getName();
    $handler_settings = $field_config->getSetting('handler_settings');
    $target_bundles = is_array($handler_settings['target_bundles']) ?
      array_keys($handler_settings['target_bundles']) : [];
      $paragraph_type_storage = $this->entityTypeManager
      ->getStorage('paragraphs_type');
    $all_paragraph_types = array_keys($this->entityTypeBundleInfo->getBundleInfo('paragraph'));



    if (empty($target_bundles)) {

      /**
       * if there are no target bundles defined in the field config, then we
       * default to target them all
       */
      $target_bundles = $all_paragraph_types;
    } else if ($handler_settings['negate']) {

      /**
       * if "Exclude the selected types" is set, then we only target bundles
       * other than the ones defined in the field config
       */
      $target_bundles = array_diff($all_paragraph_types, $target_bundles);
    }

    $paragraphs_types = $paragraph_type_storage->loadMultiple($target_bundles);

    //@todo: Add access checks

    $groups = $paragraphs_browser_type->groupManager()->getDisplayGroups();

    $mapped_items = array();

    foreach($groups as $group) {
      $mapped_items[$group->getId()] = array();
    }


    foreach($target_bundles as $bundle) {
      $group_machine_name = $paragraphs_browser_type->getGroupMap($bundle);

      if(isset($mapped_items[$group_machine_name], $groups[$group_machine_name])) {
        $mapped_items[$group_machine_name][] = $paragraphs_types[$bundle];
      }
      else {
        $mapped_items['_na'][] = $paragraphs_types[$bundle];
      }
    }
    $mapped_items = array_filter($mapped_items);


    $form['#attached']['library'][] = 'core/drupal.states';
    $form['paragraph_types'] = array(
      '#type' => 'container',
      '#theme_wrappers' => array('paragraphs_browser_wrapper'),
    );

    //@todo: Make filter display optional
    //@todo: Make categories optional.
    $options = array('all' => 'All');
    foreach(array_intersect_key($groups, $mapped_items) as $group_machine_name => $group) {
      $options[$group_machine_name] = $group->getLabel();
    }
    $form['paragraph_types']['filters'] = array(
      '#title' => 'Filter',
      '#type' => 'select',
      '#options' => $options
    );
    $form['paragraph_types']['pb_modal_text'] = array(
      '#title' => $this->t('Search'),
      '#type' => 'textfield',
      '#size' => 20,
      '#placeholder' => $this->t('simple paragraph ... '),
    );

    foreach ($mapped_items as $group_machine_name => $items) {


      $form['paragraph_types'][$group_machine_name] = array(
        '#type' => 'container',
        '#attributes' => array('class' => array($group_machine_name)),
      );
      $form['paragraph_types'][$group_machine_name]['label'] = array(
        '#type' => 'markup',
        '#markup' => '<h2>' . $groups[$group_machine_name]->getLabel() . '</h2>',
      );
      foreach($items as $paragraph_type) {
        $element = array(
          '#theme' => 'paragraphs_browser_paragraph_type'
        );
        $element['label'] = array(
          '#markup' => $paragraph_type->label()
        );
        if($description = $paragraph_type->getThirdPartySetting('paragraphs_browser', 'description', $default = NULL)) {
          $element['description'] = array(
            '#markup' => $description,
          );
        }
        if($image_path = $paragraph_type->getThirdPartySetting('paragraphs_browser', 'image_path', $default = NULL)) {
          $element['image_path'] = array(
            '#markup' => file_create_url($image_path),
          );
        }

        $form['#parents'] = (isset($form['#parents'])) ? $form['#parents'] : [];

        $id_prefix = implode('-', array_merge($form['#parents'], array($field_name)));
        $wrapper_id = Html::getUniqueId($id_prefix . '-add-more-wrapper');
        $element['add_more']['add_more_button_' . $paragraph_type->id()] = array(
          '#type' => 'submit',
          '#name' => strtr($id_prefix, '-', '_') . '_' . $paragraph_type->id() . '_add_more',
          '#value' => $this->t('Add'),
          '#attributes' => array('class' => array('field-add-more-submit')),
          '#limit_validation_errors' => array(),
          '#submit' => array(array(get_class($this), 'addMoreSubmit')),
          '#ajax' => array(
            'callback' => array(get_class($this), 'addMoreAjax'),
            'wrapper' => $wrapper_id,
            'effect' => 'fade',
          ),

          '#bundle_machine_name' => $paragraph_type->id(),
        );
        $form['paragraph_types'][$group_machine_name][$paragraph_type->id()] = $element;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public static function addMoreAjax(array $form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();
    $uuid = $build_info['uuid'];
    $response = new AjaxResponse();

    $command = new AddParagraphTypeCommand($uuid, $form_state->getTriggeringElement()['#bundle_machine_name']);
    $response->addCommand($command);

//    return $element;
    $command = new CloseModalDialogCommand();
    $response->addCommand($command);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }
}
