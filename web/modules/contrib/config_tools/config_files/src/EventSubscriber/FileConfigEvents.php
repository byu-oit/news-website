<?php

namespace Drupal\config_files\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Serialization\Yaml;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handle saving and deleting of config.
 */
class FileConfigEvents implements EventSubscriberInterface {

  /**
   * Check if debugging is on.
   */
  public function debugging() {
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
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['saveConfig', 10];
    $events[ConfigEvents::DELETE][] = ['deleteConfig', 10];
    return $events;
  }

  /**
   * Save config in prefered directory.
   *
   * Writes a file to the configured active config directory when a
   * ConfigEvents::SAVE event is dispatches.
   *
   * @param ConfigCrudEvent $event
   *   Event when saving.
   */
  public function saveConfig(ConfigCrudEvent $event) {
    if ($this->disabled() === 1) {
      return FALSE;
    }

    $config = \Drupal::config('config_files.config');
    $active_dir = $config->get('directory');
    $get_config = $event->getConfig();

    $original = $get_config->getOriginal();
    $saved = $get_config->getRawData();

    if ($is_updated = ($original === $saved)) {
      if ($this->debugging() === 1) {
        \Drupal::logger('config_files')->notice('Config is not updated, do nothing');
      }
      return FALSE;
    }

    if ($active_dir && file_prepare_directory($active_dir)) {
      $file_name = $get_config->getName() . '.yml';

      $yml = Yaml::encode($get_config->getRawData());
      file_put_contents($active_dir . '/' . $file_name, $yml, FILE_EXISTS_REPLACE);
      if ($this->debugging() === 1) {
        \Drupal::logger('config_files')->notice('Configuration for @config_name written to @active_dir', [
          '@config_name' => $get_config->getName(),
          '@active_dir' => $active_dir,
        ]);
      }
      \Drupal::state()->set('config_files_write', TRUE);
    }
  }

  /**
   * When deleting config.
   *
   * Deletes files from the configured active config directory when a
   * ConfigEvents::DELETE event is dispatched.
   *
   * @param ConfigCrudEvent $event
   *   Event when deleting.
   */
  public function deleteConfig(ConfigCrudEvent $event) {
    if ($this->disabled() === 1) {
      return FALSE;
    }

    $config = \Drupal::config('config_files.config');
    $active_dir = $config->get('directory');
    if ($active_dir) {
      $object = $event->getConfig();
      $file_name = $object->getName() . '.yml';
      unlink($active_dir . '/' . $file_name);
    }
  }

}
