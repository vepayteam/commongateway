<?php

use yii\db\Migration;

/**
 * Class m200123_065949_rename
 */
class m200123_065949_rename extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('asn','antifraud_asn');
        $this->renameTable('fingerprint_hashes','antifraud_hashes');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('antifraud_asn','asn');
        $this->renameTable('antifraud_hashes','fingerprint_hashes');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200123_065949_rename cannot be reverted.\n";

        return false;
    }
    */
}
