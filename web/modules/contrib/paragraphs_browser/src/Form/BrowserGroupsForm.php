<?php

/**
 * @file
 * Contains \Drupal\paragraphs_browser\Form\BrowserGroupsForm.
 */

namespace Drupal\paragraphs_browser\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\search_api\UnsavedConfigurationInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CleanupUrlAliases.
 *
 * @package Drupal\paragraphs_browser\Form
 */
class BrowserGroupsForm extends EntityForm {

  /**
   * The index for which the fields are configured.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $entity;

  /**
   * The shared temporary storage for unsaved search indexes.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;


  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a CropWidgetForm object.
   *
   * @param \Drupal\user\SharedTempStoreFactory $temp_store_factory
   *   The factory for shared temporary storages.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(SharedTempStoreFactory $temp_store_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStore = $temp_store_factory->get('entity_browser_type');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $temp_store_factory = $container->get('user.shared_tempstore'),
      $entity_type_manager = $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraphs_browser_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $browser_type = $this->entity;
    $form = parent::buildForm($form, $form_state);
    $form['groups'] = array(
      '#type' => 'table',
      '#header' => array('Label', 'Machine', 'Weight', ''),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'element-weight',
        ),
//        array(
//          'action' => 'match',
//          'relationship' => 'parent',
//          'group' => 'field-parent',
//          'subgroup' => 'field-parent',
//          'source' => 'field-name',
//        ),
      ),
    );

    foreach($browser_type->groupManager()->getGroups() as $group) {
      $row = array();
      $row['#attributes']['class'][] = 'draggable';
      $row['#weight'] = $group->getWeight();
      $row['label'] = array(
        '#type' => 'item',
        '#markup' => $group->getLabel(),
        '#default_value' => $group->getLabel(),
      );
      $row['machine_name'] = array(
        '#type' => 'item',
        '#markup' => $group->getId(),
        '#default_value' => $group->getId(),
      );
      $row['weight'] = array(
        '#title' => 'Weight',
        '#type' => 'textfield',
        '#default_value' => $group->getWeight(),
        '#attributes' => array(
          'class' => array('element-weight')
        )
      );
      $operations = array(
        'edit' => array(
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('paragraphs_browser.paragraphs_browser_type.group_edit_form', array('paragraphs_browser_type' => $browser_type->id(), 'group_machine_name' => $group->getId()))
        ),
        'delete' => array(
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('paragraphs_browser.paragraphs_browser_type.group_delete_form', array('paragraphs_browser_type' => $browser_type->id(), 'group_machine_name' => $group->getId())),
        ),
      );
      $row['operations'] = array(
        '#type' => 'operations',
        '#links' => $operations,
      );
      $form['groups'][$group->getId()] = $row;
    }
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );

    $form['actions'] = $this->actionsElement($form, $form_state);
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = array(
      'submit' => array(
        '#type' => 'submit',
        '#value' => $this->t('Save changes'),
        '#button_type' => 'primary',
        '#submit' => array('::submitForm', '::save'),
      ),
    );
    if ($this->entity instanceof UnsavedConfigurationInterface && $this->entity->hasChanges()) {
      $actions['cancel'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#button_type' => 'danger',
        '#submit' => array('::cancel'),
      );
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $paragraphs_browser = $this->entity;
    foreach($form_state->getValue('groups') as $value) {
      $paragraphs_browser->groupManager()->AddGroup($value['machine_name'], $value['label'], $value['weight']);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $paragraphs_browser = $this->entity;
    $changes = TRUE;
    if ($paragraphs_browser instanceof UnsavedConfigurationInterface) {
      if ($paragraphs_browser->hasChanges()) {
        $paragraphs_browser->savePermanent();
      }
      else {
        $paragraphs_browser->discardChanges();
        $changes = FALSE;
      }
    }
    else {
      $paragraphs_browser->save();
    }

    if ($changes) {
      drupal_set_message($this->t('The changes were successfully saved.'));
    }
    else {
      drupal_set_message($this->t('No values were changed.'));
    }

    return SAVED_UPDATED;
  }

  /**
   * Cancels the editing of the index's fields.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function cancel(array &$form, FormStateInterface $form_state) {
    if ($this->entity instanceof UnsavedConfigurationInterface && $this->entity->hasChanges()) {
      $this->entity->discardChanges();
    }

    $form_state->setRedirectUrl($this->entity->toUrl('canonical'));
  }


}
