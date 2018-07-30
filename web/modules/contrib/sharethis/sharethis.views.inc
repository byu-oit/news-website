<?php

/**
 * @file
 * Provide views data for tracker.module.
 */

/**
 * Implements hook_views_data().
 */
function sharethis_views_data() {
  $data = array();
  // ShareThis link.
  $data['node']['sharethis_node'] = array(
    'title' => t('ShareThis Link'),
    'help' => t('Link provided by the ShareThis service.'),
    'field' => array(
      'id' => 'sharethis_node',
      'help' => t('Show Sharethis for a particular node.'),

    ),
  );
  return $data;
}
