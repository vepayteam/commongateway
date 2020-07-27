<?php

use app\services\auth\models\UserToken;
use yii\db\Migration;

/**
 * Class m200724_063702_create_auth_login_tokens
 */
class m200724_063702_create_auth_login_tokens extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(UserToken::tableName(), [
            'ID' => $this->primaryKey()->unsigned(),
            'UserId' => $this->integer()->notNull(),
            'IP' => $this->string()->notNull(),
            'Token' => $this->string()->notNull(),
            'RoleNames' => $this->string()->notNull(),
            'Scope' => $this->string()->notNull(),
            'RefreshToken' => $this->string()->notNull(),
            'DateExpires' => $this->integer()->notNull(),
            'DateExpiresRefresh' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(UserToken::tableName());
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200724_063702_create_auth_login_tokens cannot be reverted.\n";

        return false;
    }
    */
}
