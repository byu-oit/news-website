<?php

namespace Drupal\migrate_manifest;

use Drupal\migrate\MigrateMessageInterface;

class DrushLogMigrateMessage implements MigrateMessageInterface {

  /**
   * @inheritdoc
   */
  public function display($message, $type = 'status') {
    drush_log($message, $type);
  }

}
