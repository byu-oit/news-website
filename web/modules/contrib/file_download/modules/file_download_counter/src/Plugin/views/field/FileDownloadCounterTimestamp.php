<?php

namespace Drupal\file_download_counter\Plugin\views\field;

use Drupal\views\Plugin\views\field\Date;
use Drupal\Core\Session\AccountInterface;

/**
 * Field handler to display the most recent time the node has been viewed.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("node_counter_timestamp")
 */
class FileDownloadCounterTimestamp extends Date {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('view file download counter');
  }

}
