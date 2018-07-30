<?php

/**
 * @file
 * Builds placeholder replacement tokens for node visitor file_download_counter.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function file_download_counter_token_info() {
  $file['total-count'] = [
    'name' => t("Number of downloads"),
    'description' => t("The number of visitors who have downloaded the file."),
  ];
  $file['day-count'] = [
    'name' => t("Downloads today"),
    'description' => t("The number of visitors who have downloaded the file today."),
  ];
  $file['last-view'] = [
    'name' => t("Last download"),
    'description' => t("The date on which a visitor last downloaded the file."),
    'type' => 'date',
  ];

  return [
    'tokens' => ['file' => $file],
  ];
}

/**
 * Implements hook_tokens().
 */
function file_download_counter_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  $replacements = [];

  if ($type == 'file' & !empty($data['file'])) {
    $file = $data['file'];

    foreach ($tokens as $name => $original) {
      if ($name == 'total-count') {
        $file_download_counter = file_download_counter_get($file->id());
        $replacements[$original] = $file_download_counter['totalcount'];
      }
      elseif ($name == 'day-count') {
        $file_download_counter = file_download_counter_get($file->id());
        $replacements[$original] = $file_download_counter['daycount'];
      }
      elseif ($name == 'last-view') {
        $file_download_counter = file_download_counter_get($file->id());
        $replacements[$original] = format_date($file_download_counter['timestamp']);
      }
    }

    if ($created_tokens = $token_service->findWithPrefix($tokens, 'last-view')) {
      $file_download_counter = file_download_counter_get($file->id());
      $replacements += $token_service->generate('date', $created_tokens, ['date' => $file_download_counter['timestamp']], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
