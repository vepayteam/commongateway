<?php

use yii\db\Migration;

/**
 * Class m200302_130707_addactfields
 */
class m200302_130707_addactfields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('act_mfo', 'BeginOstatokVoznag', $this->bigInteger(20)->notNull()->defaultValue(0));
        $this->addColumn('act_mfo', 'EndOstatokVoznag', $this->bigInteger(20)->notNull()->defaultValue(0));
        $this->addColumn('act_mfo', 'SumPodlejUderzOspariv', $this->bigInteger(20)->notNull()->defaultValue(0));
        $this->addColumn('act_mfo', 'SumPodlejVozmeshOspariv', $this->bigInteger(20)->notNull()->defaultValue(0));
        $this->addColumn('act_mfo', 'SumPerechKontrag', $this->bigInteger(20)->notNull()->defaultValue(0));
        $this->addColumn('act_mfo', 'SumPerechObespech', $this->bigInteger(20)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('act_mfo', 'BeginOstatokVoznag');
        $this->dropColumn('act_mfo', 'EndOstatokVoznag');
        $this->dropColumn('act_mfo', 'SumPodlejUderzOspariv');
        $this->dropColumn('act_mfo', 'SumPodlejVozmeshOspariv');
        $this->dropColumn('act_mfo', 'SumPerechKontrag');
        $this->dropColumn('act_mfo', 'SumPerechObespech');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200302_130707_addactfields cannot be reverted.\n";

        return false;
    }
    */
}
