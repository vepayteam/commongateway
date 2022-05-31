<?php

use AspectMock\Kernel;

define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require __DIR__ .'/../vendor/autoload.php';

$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'cacheDir'  => '/tmp/myapp',
    'excludePaths' => [__DIR__.'/../']
//    'includePaths' => [__DIR__.'/../src']
]);