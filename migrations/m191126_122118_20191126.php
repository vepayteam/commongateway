<?php

use yii\db\Migration;

/**
 * Class m191126_122118_20191126
 */
class m191126_122118_20191126 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable('keys', [
            'ID' => $this->primaryKey()->unsigned(),
            'Value' => $this->string(250),
        ], $tableOptions);

        $this->createTable('pan_token', [
            'ID' => $this->primaryKey()->unsigned(),
            'FirstSixDigits' => $this->string(10),
            'LastFourDigits' => $this->string(10),
            'EncryptedPAN' => $this->string(250),
            'ExpDateMonth' => $this->string(10),
            'ExpDateYear' => $this->string(10),
            'CreatedDate' => $this->integer()->notNull(),
            'UpdatedDate' => $this->integer()->notNull(),
            'CryptoKeyId' => $this->integer()->notNull()
        ], $tableOptions);
        $this->createIndex('idx_CryptoKeyId', '{{%pan_token}}', 'CryptoKeyId');

        $this->createTable('crypto_keys_table', [
            'ID' => $this->primaryKey()->unsigned(),
            'EncryptedKeyValue' => $this->string(250),
            'CreatedDate' => $this->integer()->notNull(),
            'UpdatedDate' => $this->integer()->notNull(),
            'Counter' => $this->integer()->notNull()
        ], $tableOptions);
        $this->createIndex('idx_Counter', '{{%crypto_keys_table}}', 'Counter');

        $this->addColumn('cards', 'CardHolder', 'varchar(100)  AFTER `TypeCard`');
        $this->addColumn('cards', 'IdPan', 'INT(10) unsigned DEFAULT 0 NOT NULL AFTER `CardHolder`');
        $this->addColumn('cards', 'IdBank', 'INT(10) unsigned DEFAULT 0 NOT NULL AFTER `IdPan`');

        $this->createIndex('idx_IdPan', '{{%cards}}', 'IdPan');
        $this->createIndex('idx_IdBank', '{{%cards}}', 'IdBank');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('keys');
        $this->dropTable('pan_token');
        $this->dropTable('crypto_keys_table');
        $this->dropColumn('cards', 'CardHolder');
        $this->dropColumn('cards', 'IdPan');
        $this->dropColumn('cards', 'IdBank');
        $this->dropIndex('cards', 'idx_IdPan');
        $this->dropIndex('cards', 'idx_IdBank');
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191126_122118_20191126 cannot be reverted.\n";

        return false;
    }
    */
}
