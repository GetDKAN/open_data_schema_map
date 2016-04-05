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

return array(
    'Symfony\\Component\\Serializer\\' => array($library_path),
);
