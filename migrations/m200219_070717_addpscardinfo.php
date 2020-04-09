<?php

use yii\db\Migration;

/**
 * Class m200219_070717_addpscardinfo
 */
class m200219_070717_addpscardinfo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet', 'CardHolder','varchar(100) DEFAULT NULL COMMENT \'derjatel karty\' AFTER `CardType`');
        $this->addColumn('pay_schet', 'CardExp', 'int(4) unsigned NOT NULL DEFAULT \'0\' COMMENT \'srok deistvia karty - MMYY\' AFTER `CardHolder`');
}

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet', 'CardHolder');
        $this->dropColumn('pay_schet', 'CardExp');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200219_070717_addpscardinfo cannot be reverted.\n";

        return false;
    }
    */
}
