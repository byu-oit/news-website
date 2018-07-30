<?php

/**
 * @file
 * Contains \Drupal\paragraphs_browser\Form\GroupEditForm.
 */

namespace Drupal\paragraphs_browser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs_browser\BrowserGroupList;
use Drupal\paragraphs_browser\BrowserTypeInterface;
use Drupal\paragraphs_browser\Entity\BrowserType;

/**
 * Class CleanupUrlAliases.
 *
 * @package Drupal\paragraphs_browser\Form
 */
class GroupEditForm extends FormBase {

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
    return 'paragraphs_browser_group_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, BrowserTypeInterface $paragraphs_browser_type = null, $group_machine_name = '') {
    /** @var BrowserType $paragraphs_browser_type */
    $this->entity = $paragraphs_browser_type;
    /** @var BrowserGroupList $groups */
    $groups = $paragraphs_browser_type->groupManager();
    // Build the form.
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $groups->getGroup($group_machine_name)->getLabel(),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $group_machine_name,
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ),
      '#disabled' => TRUE
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#submit' => array('::submitForm', '::save'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $group = $this->entity->groupManager()->getGroup($form_state->getValue('id'));
    $group->setLabel($form_state->getValue('label'));
    $form_state->setRedirectUrl($this->entity->toUrl('groups-form'));
  }

  /**
   * Secondary submit handler to save entity after group has been updated
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
  }


}
