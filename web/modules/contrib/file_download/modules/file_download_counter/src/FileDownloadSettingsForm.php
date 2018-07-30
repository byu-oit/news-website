<?php

namespace Drupal\file_download_counter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure statistics settings for this site.
 */
class FileDownloadSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\user\StatisticsSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);

    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'file_download_counter_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['file_download_counter.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('file_download_counter.settings');

    // Content counter settings.
    $form['content'] = [
      '#type' => 'details',
      '#title' => t('File download counter settings'),
      '#open' => TRUE,
    ];
    $form['content']['file_download_counter_count_downloads'] = [
      '#type' => 'checkbox',
      '#title' => t('Count downloads'),
      '#default_value' => $config->get('count_downloads'),
      '#description' => t('Increment a counter each time content is downloaded.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('file_download_counter.settings')
      ->set('count_downloads', $form_state->getValue('file_download_counter_count_downloads'))
      ->save();

    // The popular statistics block is dependent on these settings, so clear the
    // block plugin definitions cache.
    if ($this->moduleHandler->moduleExists('block')) {
      \Drupal::service('plugin.manager.block')->clearCachedDefinitions();
    }

    parent::submitForm($form, $form_state);
  }

}
