<?php

namespace Drupal\paragraphs_browser\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs_browser\BrowserTypeInterface;

/**
 * Class CleanupUrlAliases.
 *
 * @package Drupal\paragraphs_browser\Form
 */
class GroupDeleteConfirm extends FormBase {

  /**
   * The index for which the fields are configured.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraphs_browser_group_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BrowserTypeInterface $paragraphs_browser_type = null, $group_machine_name = '') {
    $this->entity = $paragraphs_browser_type;

    $form['id'] = array('#type' => 'hidden', '#value' => $group_machine_name);

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Confirm Deletion'),
      '#button_type' => 'primary',
      '#submit' => array('::submitForm', '::save'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->groupManager()->removeGroup($form_state->getValue('id'));
    $form_state->setRedirectUrl($this->entity->toUrl('groups-form'));
  }

  /**
   * Secondary submit handler, saves entity after group has been removed
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
  }

  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {

  }
}
