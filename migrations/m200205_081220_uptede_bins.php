<?php

use yii\db\Migration;

/**
 * Class m200205_081220_uptede_bins
 */
class m200205_081220_uptede_bins extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
//        $this->dropTable('antifraud_bin_banks');
        $path = Yii::getAlias('@app') . '/models/antifraud/data/antifraud_bin_banks.sql';
        $this->execute(file_get_contents($path));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200205_081220_uptede_bins cannot be reverted.\n";

        return false;
    }
    */
}
