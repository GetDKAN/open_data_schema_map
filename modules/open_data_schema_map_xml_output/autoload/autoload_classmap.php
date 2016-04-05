<?php

$library_path=implode(
  '/',
  array(
    DRUPAL_ROOT,
    drupal_get_path('profile', 'dkan'),
    'libraries',
    'symfonyserializer',
  )
);

$vendorDir = $library_path;
$baseDir = $vendorDir;

return array();
