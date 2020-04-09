<?php

use yii\db\Migration;

/**
 * Class m200124_145315_add_column
 */
class m200124_145315_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('antifraud_asn', 'is_black', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('antifraud_asn', 'is_black');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200124_145315_add_column cannot be reverted.\n";

        return false;
    }
    */
}
