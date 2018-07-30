<?php

namespace Drupal\git_config\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Check config changes, and commit it to repo.
 */
class GitConfigEvents implements EventSubscriberInterface {

  /**
   * Get state for git commit.
   */
  private function configWrite() {
    $config_files_write = \Drupal::state()->get('config_files_write');
    return $config_files_write;
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
  public static function getSubscribedEvents() {
    $events['config.save'] = ['saveConfig'];
    $events['config.delete'] = ['deleteConfig'];
    return $events;
  }

  /**
   * Save the changed config.
   */
  public function saveConfig(ConfigCrudEvent $event) {
    if ($this->disabled() === 1) {
      return FALSE;
    }
    $config = \Drupal::config('git_config.config');
    $private_key = $config->get('private_key');
    $git_url = $config->get('git_url');
    $git_username = $config->get('git_username');
    $git_email = $config->get('git_email');
    $active_dir = \Drupal::config('config_files.config')->get('directory');

    if ($this->configWrite() === FALSE) {
      return FALSE;
    }

    if ($active_dir && !empty($private_key) && !empty($git_url) && !empty($git_username) && !empty($git_email)) {
      $wrapper = new GitWrapper();
      $wrapper->setPrivateKey($config->get('private_key'));
      $git = $wrapper->workingCopy($active_dir);

      $get_config = $event->getConfig();

      $original = $get_config->getOriginal();
      $saved = $get_config->getRawData();

      if ($is_updated = ($original === $saved)) {
        if ($this->debugging() === 1) {
          \Drupal::logger('git_config')
            ->notice('Config is not updated, do nothing');
        }
        return FALSE;
      }

      $config_name = $get_config->getName();
      $file_name = $config_name . '.yml';

      try {
        $user = \Drupal::currentUser();
        $name = $user->getAccount()->getDisplayName();
        $git->add($file_name)
          ->config('user.name', $git_username)
          ->config('user.email', $git_email)
          ->commit([
            'm' => "Configuration for $config_name updated by $name",
            'a' => TRUE,
            'author' => $name . ' <' . $user->getAccount()->getEmail() . '>',
          ]);

        $this->gitPushQue($name, $config_name);

        if ($this->debugging() === 1) {
          \Drupal::logger('git_config')->notice('Configuration for @config_name updated by @name', [
            '@config_name' => $config_name,
            '@name' => $name,
          ]);
        }
      }
      catch (GitException $e) {
        if ($this->debugging() === 1) {
          \Drupal::logger('git_config')->notice($e->getMessage());
        }
      }
      \Drupal::state()->set('config_files_write', FALSE);
    }
  }

  /**
   * Delete removed config from repo.
   */
  public function deleteConfig(ConfigCrudEvent $event) {
    if ($this->disabled() === 1) {
      return FALSE;
    }
    $config = \Drupal::config('git_config.config');
    $private_key = $config->get('private_key');
    $git_url = $config->get('git_url');
    $git_username = $config->get('git_username');
    $git_email = $config->get('git_email');
    $active_dir = \Drupal::config('config_files.config')->get('directory');
    if ($active_dir && !empty($private_key) && !empty($git_url)) {
      $wrapper = new GitWrapper();
      $wrapper->setPrivateKey($config->get('private_key'));
      $git = $wrapper->workingCopy($active_dir);

      $get_config = $event->getConfig();
      $file_name = $get_config->getName() . '.yml';

      try {
        $user = \Drupal::currentUser();
        $name = $user->getAccount()->getDisplayName();
        $git->rm($file_name)
          ->config('user.name', $git_username)
          ->config('user.email', $git_email)
         ->commit(t('Removed by @name', ['@name' => $name]));
      }
      catch (GitException $e) {
        if ($this->debugging() === 1) {
          \Drupal::logger('git_config')->warning($e->getMessage());
        }
      }
    }
  }

  /**
   * Add the task to the queue.
   */
  public function gitPushQue($user, $config) {
    $queue = \Drupal::queue('git_config_tasks_git_push');
    $data = ['user' => $user, 'config' => $config];
    $queue->createItem($data);
  }

}
