<?php

$vendorDir = dirname(__DIR__);

return array (
  'yiisoft/yii2-bootstrap' => 
  array (
    'name' => 'yiisoft/yii2-bootstrap',
    'version' => '2.0.10.0',
    'alias' => 
    array (
      '@yii/bootstrap' => $vendorDir . '/yiisoft/yii2-bootstrap/src',
    ),
  ),
  'yiisoft/yii2-swiftmailer' => 
  array (
    'name' => 'yiisoft/yii2-swiftmailer',
    'version' => '2.1.2.0',
    'alias' => 
    array (
      '@yii/swiftmailer' => $vendorDir . '/yiisoft/yii2-swiftmailer/src',
    ),
  ),
  'toriphes/yii2-console-runner' => 
  array (
    'name' => 'toriphes/yii2-console-runner',
    'version' => '1.2.1.0',
    'alias' => 
    array (
      '@toriphes/console' => $vendorDir . '/toriphes/yii2-console-runner',
    ),
  ),
  'yiisoft/yii2-debug' => 
  array (
    'name' => 'yiisoft/yii2-debug',
    'version' => '2.1.13.0',
    'alias' => 
    array (
      '@yii/debug' => $vendorDir . '/yiisoft/yii2-debug/src',
    ),
  ),
  'yiisoft/yii2-gii' => 
  array (
    'name' => 'yiisoft/yii2-gii',
    'version' => '2.1.4.0',
    'alias' => 
    array (
      '@yii/gii' => $vendorDir . '/yiisoft/yii2-gii/src',
    ),
  ),
  'yiisoft/yii2-faker' => 
  array (
    'name' => 'yiisoft/yii2-faker',
    'version' => '2.0.4.0',
    'alias' => 
    array (
      '@yii/faker' => $vendorDir . '/yiisoft/yii2-faker',
    ),
  ),
  'yiisoft/yii2-queue' => 
  array (
    'name' => 'yiisoft/yii2-queue',
    'version' => '2.2.1.0',
    'alias' => 
    array (
      '@yii/queue' => $vendorDir . '/yiisoft/yii2-queue/src',
      '@yii/queue/amqp' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/amqp',
      '@yii/queue/amqp_interop' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/amqp_interop',
      '@yii/queue/beanstalk' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/beanstalk',
      '@yii/queue/db' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/db',
      '@yii/queue/file' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/file',
      '@yii/queue/gearman' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/gearman',
      '@yii/queue/redis' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/redis',
      '@yii/queue/sync' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/sync',
      '@yii/queue/sqs' => $vendorDir . '/yiisoft/yii2-queue/src/drivers/sqs',
    ),
  ),
  '2amigos/qrcode-library' => 
  array (
    'name' => '2amigos/qrcode-library',
    'version' => '1.1.2.0',
    'alias' => 
    array (
      '@Da/QrCode' => $vendorDir . '/2amigos/qrcode-library/src',
    ),
  ),
  'yiisoft/yii2-redis' => 
  array (
    'name' => 'yiisoft/yii2-redis',
    'version' => '2.0.13.0',
    'alias' => 
    array (
      '@yii/redis' => $vendorDir . '/yiisoft/yii2-redis/src',
    ),
  ),
  'qfsx/yii2-curl' => 
  array (
    'name' => 'qfsx/yii2-curl',
    'version' => '9999999-dev',
    'alias' => 
    array (
      '@qfsx/yii2/curl' => $vendorDir . '/qfsx/yii2-curl/src',
    ),
  ),
  'kartik-v/yii2-mpdf' => 
  array (
    'name' => 'kartik-v/yii2-mpdf',
    'version' => '9999999-dev',
    'alias' => 
    array (
      '@kartik/mpdf' => $vendorDir . '/kartik-v/yii2-mpdf/src',
    ),
  ),
);
