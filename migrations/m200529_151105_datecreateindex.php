<?php

use yii\db\Migration;

/**
 * Class m200529_151105_datecreateindex
 */
class m200529_151105_datecreateindex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('DateCreate_idx', 'pay_schet', ['DateCreate', 'Status', 'IdUsluga']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('DateCreate_idx', 'pay_schet');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200529_151105_datecreateindex cannot be reverted.\n";

        return false;
    }
    */
}
