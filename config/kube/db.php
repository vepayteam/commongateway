<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('DATABASE_DSN', true),
    'username' => getenv('DATABASE_USER', true),
    'password' => getenv('DATABASE_USER_PASSWORD', true),
    'charset' => 'utf8',
    'on afterOpen' => function($event) {
        $event->sender->createCommand("SET NAMES utf8;")->execute();
    }
	
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',	
];
