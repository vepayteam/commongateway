<?php

use yii\db\Migration;

/**
 * Class m200303_094825_addparetneroption
 */
class m200303_094825_addparetneroption extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'TypeMerchant', $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->comment('type merchanta: 0 - merchant 1 - partner')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'TypeMerchant');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200303_094825_addparetneroption cannot be reverted.\n";

        return false;
    }
    */
}
