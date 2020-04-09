<?php

use yii\db\Migration;

/**
 * Class m191217_073451_add_collumn
 */
class m191217_073451_add_collumn extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('sms','partner_id', $this->integer());
        $this->createIndex('code_idx', 'sms', ['code', 'partner_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('code_idx', 'sms');
        $this->dropColumn('sms', 'partner_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191217_073451_add_collumn cannot be reverted.\n";

        return false;
    }
    */
}
