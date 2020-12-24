<?php

use yii\db\Migration;

/**
 * Class m201223_142701_user_token
 */
class m201223_142701_user_token extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_token', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'token' => $this->string(),
            'created_at' => $this->dateTime(),
            'valid_until' => $this->dateTime(),
        ]);

        $this->createTable('key_users_token',[
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'token' => $this->string(),
            'created_at' => $this->dateTime(),
            'valid_until' => $this->dateTime(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_token');
        $this->dropTable('key_users_token');
    }

}
