<?php

/**
 * @file
 * Contains custom views support and definitions.
 */

/**
 * Implements hook_views_data_alter().
 */
function workflow_tools_views_data_alter(array &$data) {

  $data['node_field_data']['moderation_links'] = [
    'title' => t('Custom Moderation Links'),
    'field' => [
      'help' => t('Displays links to transition nodes.'),
      'id' => 'moderation_links',
    ],
  ];

}
