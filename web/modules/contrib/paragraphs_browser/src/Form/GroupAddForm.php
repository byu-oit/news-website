<?php

/**
 * @file
 * Contains \Drupal\paragraphs_browser\Form\GroupAddForm.
 */

namespace Drupal\paragraphs_browser\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GroupAddForm.
 *
 * @package Drupal\paragraphs_browser\Form
 */
class GroupAddForm extends EntityForm {

  /**
   * The index for which the fields are configured.
   *
   * @var \Drupal\paragraphs_browser\Entity\BrowserType
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
    return 'paragraphs_browser_group_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['paragraphs_browser.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    // Build the form.
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => '',
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => '',
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ),
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Add'),
      '#button_type' => 'primary',
      '#submit' => array('::submitForm', '::save'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $group_list = $this->entity->groupManager();

    $group_list->addGroup($form_state->getValue('id'), $form_state->getValue('label'));

    $form_state->setRedirectUrl($this->entity->toUrl('groups-form'));
  }
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
  }
  function exists($value) {
    $this->entity->groupManager()->getGroup($value);
  }

  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {

  }
}
