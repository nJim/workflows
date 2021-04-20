<?php

$databases = [];
$config_directories = [];
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
$settings['hash_salt'] = 'fxpNIi_ON-6_KJOjy6kwN6acClO0a47jGrSxFC_qrZ9tCAoRZgEqAcr18r9I5DLQiHZM_u_Y4g';
$settings['config_sync_directory'] = DRUPAL_ROOT . "/../config/sync";
$settings['entity_update_batch_size'] = 50;
$settings['entity_update_backup'] = TRUE;
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$databases['default']['default'] = array (
  'database' => 'drupal9',
  'username' => 'drupal9',
  'password' => 'drupal9',
  'prefix' => '',
  'host' => 'database',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
