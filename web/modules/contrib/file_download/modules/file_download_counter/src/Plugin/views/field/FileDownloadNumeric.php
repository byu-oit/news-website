<?php

namespace Drupal\file_download_counter\Plugin\views\field;

use Drupal\views\Plugin\views\field\NumericField;
use Drupal\Core\Session\AccountInterface;

/**
 * Field handler to display numeric values from the statistics module.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("statistics_numeric")
 */
class FileDownloadNumeric extends NumericField {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('view file download counter');
  }

}
