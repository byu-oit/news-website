<?php

namespace Drupal\config_tools\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for config_files.
 */
class ConfigToolsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'config_tools.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_tools_settings_form';
  }

  /**
   * Build the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('config_tools.config');

    $form['disabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Config tools disabled'),
      '#description' => t('Disables config_tools, git_config and files_config.'),
      '#default_value' => $config->get('disabled'),
    ];

    $form['debug'] = [
      '#type' => 'checkbox',
      '#title' => t('Use debug logging'),
      '#description' => t('Logs notices to the Drupal logger.'),
      '#default_value' => $config->get('debug'),
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

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $debug = $form_state->getValue('debug');
    $disabled = $form_state->getValue('disabled');

    $this->config('config_tools.config')
      ->set('debug', $debug)
      ->set('disabled', $disabled)
      ->save();
  }

}
