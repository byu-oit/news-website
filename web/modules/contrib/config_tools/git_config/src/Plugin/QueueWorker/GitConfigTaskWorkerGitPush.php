<?php

namespace Drupal\git_config\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;

/**
 * Processes Tasks for My Module.
 *
 * @QueueWorker(
 *   id = "git_config_tasks_git_push",
 *   title = @Translation("Git Push Task Worker"),
 *   cron = {"time" = 1}
 * )
 */
class GitConfigTaskWorkerGitPush extends QueueWorkerBase {

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
  public function processItem($data) {
    if ($this->disabled() === 1) {
      return FALSE;
    }

    // Process $data here.
    $config = \Drupal::config('git_config.config');
    $private_key = $config->get('private_key');
    $git_url = $config->get('git_url');
    $git_username = $config->get('git_username');
    $git_email = $config->get('git_email');
    $active_dir = \Drupal::config('config_files.config')->get('directory');
    if ($active_dir && !empty($private_key) && !empty($git_url) && !empty($git_username) && !empty($git_email)) {

      $wrapper = new GitWrapper();
      $wrapper->setPrivateKey($config->get('private_key'));
      $git = $wrapper->workingCopy($active_dir);

      try {
        $git->config('user.name', $git_username)
          ->config('user.email', $git_email)
          ->push();
        if ($this->debugging() === 1) {
          \Drupal::logger('git_config')->notice('git commits pushed');
        }
        // Deletes remaining queues, we do not need them. I know, not so nifty.
        $query = \Drupal::database()->delete('queue');
        $query->condition('name', 'git_config_tasks_git_push');
        $query->execute();
      }
      catch (GitException $e) {
        if ($this->debugging() === 1) {
          \Drupal::logger('git_config')->notice($e->getMessage());
        }
      }
    }
  }

}
