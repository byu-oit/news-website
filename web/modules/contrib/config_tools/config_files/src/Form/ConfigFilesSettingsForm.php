<?php

namespace Drupal\config_files\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Serialization\Yaml;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;

/**
 * Settings form for config_files.
 */
class ConfigFilesSettingsForm extends ConfigFormBase {

  /**
   * Get the ConfigManager.
   *
   * @var \Drupal\Core\Config\ConfigManager
   */
  protected $configManager;

  /**
   * Get the config for the form.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigManager $configManager) {
    $this->configManager = $configManager;
  }

  /**
   * Check if debugging is on.
   */
  private function debugging() {
    $config = \Drupal::config('config_tools.config');
    $debugging = $config->get('debug');
    return $debugging;
  }

  /**
   * Check if functionality should be disabled.
   */
  public function disabled() {
    $config = \Drupal::config('config_tools.config');
    $disabled = $config->get('disabled');
    return $disabled;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'config_files.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_files_settings_form';
  }

  /**
   * Build the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('config_files.config');

    $form['directory'] = [
      '#type' => 'textfield',
      '#title' => t('Active files directory'),
      '#description' => t('Provide an absolute path to a directory outside of
        the Drupal webroot. DO NOT use Drupal\'s active configuration
        directory.'),
      '#default_value' => $config->get('directory'),
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

    if (!(file_prepare_directory($form_state->getValue('directory')))) {
      $form_state->setErrorByName('directory', $this->t('Directory do not exist or is not writable'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $active_dir = $form_state->getValue('directory');

    $this->config('config_files.config')
      ->set('directory', $active_dir)
      ->save();

    if ($this->disabled() === 1) {
      return FALSE;
    }
    foreach ($this->configManager->getConfigFactory()->listAll() as $name) {
      $yml = Yaml::encode($this->configManager->getConfigFactory()->get($name)->getRawData());
      $file_name = $name . '.yml';
      file_put_contents($active_dir . '/' . $file_name, $yml, FILE_EXISTS_REPLACE);
    }

    // Commit all changes if git_config is active.
    $git_config_exists = \Drupal::moduleHandler()->moduleExists('git_config');
    if ($git_config_exists === TRUE) {
      $git_config = \Drupal::config('git_config.config');
      $private_key = $git_config->get('private_key');
      $git_username = $git_config->get('git_username');
      $git_email = $git_config->get('git_email');
      $active_dir = \Drupal::config('config_files.config')->get('directory');

      $wrapper = new GitWrapper();
      $wrapper->setPrivateKey($private_key);
      $git = $wrapper->workingCopy($active_dir);

      try {
        $user = \Drupal::currentUser();
        $name = $user->getAccount()->getDisplayName();
        $git->add('.')
          ->commit([
            'm' => "Configuration updated on by config_files export by $name",
            'a' => TRUE,
            'author' => $name . ' <' . $user->getAccount()->getEmail() . '>',
          ])
          ->config('user.name', $git_username)
          ->config('user.email', $git_email);
      }
      catch (GitException $e) {
        if ($this->debugging() === 1) {
          \Drupal::logger('git_config')->notice($e->getMessage());
        }
      }
    }

  }

}
