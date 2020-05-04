<?php

use yii\db\Migration;

/**
 * Class m200504_134934_newsupd
 */
class m200504_134934_newsupd extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('news');
        $this->createTable('news', [
            'ID' => $this->primaryKey()->unsigned(),
            'Head' => $this->string(),
            'Body' => $this->text(),
            'DateAdd' => $this->integer()->unsigned()->notNull(),
            'DateSend' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'Bank' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
            'BankId' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'BankDate' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'IsDeleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->createIndex('news_date_idx', 'news', ['DateAdd', 'DateSend', 'IsDeleted']);
        $this->createIndex('news_bank_idx', 'news', ['Bank', 'BankId']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200504_134934_newsupd cannot be reverted.\n";

        return false;
    }
    */
}
