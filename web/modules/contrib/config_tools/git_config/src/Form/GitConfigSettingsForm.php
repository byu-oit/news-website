<?php

namespace Drupal\git_config\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure git config settings.
 */
class GitConfigSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'git_config_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'git_config.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('git_config.config');
    $form['git_url'] = [
      '#type' => 'textfield',
      '#title' => t('Git URL'),
      '#default_value' => $config->get('git_url'),
      '#description' => t('Git repo to push config changes to. Must be in SSH
        format, like git@github.com:me/config.git'),
      '#required' => TRUE,
    ];
    $form['private_key'] = [
      '#type' => 'textfield',
      '#title' => t('Private SSH Key'),
      '#default_value' => $config->get('private_key'),
      '#description' => t('The file path to a private key for this git repository. The private key must be owned by the web server user with execute privileges and located in a directory accessible by the web server user.'),
      '#required' => TRUE,
    ];
    $form['git_username'] = [
      '#type' => 'textfield',
      '#title' => t('Git username'),
      '#default_value' => $config->get('git_username'),
      '#description' => t('User name for communicating with git repo'),
      '#required' => TRUE,
    ];
    $form['git_email'] = [
      '#type' => 'email',
      '#title' => t('Git email'),
      '#default_value' => $config->get('git_email'),
      '#description' => t('Email address for communicating with git repo'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';

    $form['#theme'] = 'system_config_form';
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // @todo validate that we got a git url we can commit to and that this is a private key.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('git_config.config')
      ->set('git_url', $form_state->getValue('git_url'))
      ->set('private_key', $form_state->getValue('private_key'))
      ->set('git_email', $form_state->getValue('git_email'))
      ->set('git_username', $form_state->getValue('git_username'))
      ->save();
  }

}
