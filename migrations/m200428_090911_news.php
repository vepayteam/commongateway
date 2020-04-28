<?php

use yii\db\Migration;

/**
 * Class m200428_090911_news
 */
class m200428_090911_news extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('news', [
            'ID' => $this->primaryKey()->unsigned(),
            'Head' => $this->string(),
            'Body' => $this->text(),
            'DateAdd' => $this->integer()->unsigned()->notNull(),
            'DateSend' => $this->integer()->unsigned()->notNull()
        ]);
        $this->createIndex('news_date_idx', 'news', ['DateAdd', 'DateSend']);

        $this->createTable('newsread', [
            'ID' => $this->primaryKey()->unsigned(),
            'IdNews' => $this->integer()->unsigned()->notNull(),
            'IdUser' => $this->integer()->unsigned()->notNull(),
            'DateRead' => $this->integer()->unsigned()->notNull()
        ]);
        $this->createIndex('newsread_user_idx', 'newsread', ['IdUser', 'IdNews']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('news');
        $this->dropTable('newsread');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200428_090911_news cannot be reverted.\n";

        return false;
    }
    */
}
