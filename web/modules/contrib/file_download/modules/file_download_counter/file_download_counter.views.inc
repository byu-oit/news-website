<?php

/**
 * @file
 * Provide views data for file_download_counter.module.
 */

/**
 * Implements hook_views_data().
 */
function file_download_counter_views_data() {
  $data['file_download_counter']['table']['group'] = t('File Downloads');

  $data['file_download_counter']['table']['join'] = [
    'file_managed' => [
      'left_field' => 'fid',
      'field' => 'fid',
    ],
  ];

  $data['file_download_counter']['totalcount'] = [
    'title' => t('Total downloads'),
    'help' => t('The total number of times the file has been downloaded.'),
    'field' => [
      'id' => 'file_download_counter_numeric',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['file_download_counter']['daycount'] = [
    'title' => t('Downloads today'),
    'help' => t('The total number of times the file has been downloaded today.'),
    'field' => [
      'id' => 'file_download_counter_numeric',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['file_download_counter']['timestamp'] = [
    'title' => t('Most recent download'),
    'help' => t('The most recent time the file has been downloaded.'),
    'field' => [
      'id' => 'file_download_counter_date',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'date',
    ],
    'argument' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  return $data;
}
