<?php

/**
 * @file
 * Contains custom views support and definitions.
 */

/**
 * Implements hook_views_data_alter().
 */
function app_dashboard_views_data_alter(array &$data) {

  // Field Plugin for Demo
  $data['node_field_data']['app_meta'] = [
    'title' => t('Custom App Meta'),
    'field' => [
      'help' => t('Metadata displays in app management view.'),
      'id' => 'app_meta',
    ],
  ];

}
