<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => $_ENV['DATABASE_DSN'],
    'username' => $_ENV['DATABASE_USER'],
    'password' => $_ENV['DATABASE_USER_PASSWORD'],
    'charset' => 'utf8',
    'on afterOpen' => function($event) {
        $event->sender->createCommand("SET NAMES utf8;")->execute();
    }
	
    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',	
];
