<?php

use yii\db\Migration;

/**
 * Class m200402_073312_addactbankkomis
 */
class m200402_073312_addactbankkomis extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('act_mfo', 'SumSchetComisVyplata', $this->bigInteger(20)->notNull()->defaultValue(0));
        $this->addColumn('act_mfo', 'SumSchetComisPerevod', $this->bigInteger(20)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('act_mfo', 'SumSchetComisVyplata');
        $this->dropColumn('act_mfo', 'SumSchetComisPerevod');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200402_073312_addactbankkomis cannot be reverted.\n";

        return false;
    }
    */
}
