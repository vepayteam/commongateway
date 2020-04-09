<?php

use app\models\parsers\CSVBinBank;
use yii\db\Migration;

/**
 * Class m200203_005819_add_bin_banks_table
 */
class m200203_005819_add_bin_banks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('antifraud_bin_banks', [
            'id'=>$this->primaryKey(),
            'bin'=>$this->integer(),
            'payment_system'=>$this->string(),
            'country'=>$this->string(),
        ]);

        $this->createIndex('bin', 'antifraud_bin_banks', 'bin');
        //new CSVBinBank();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('antifraud_bin_banks');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200203_005819_add_bin_banks_table cannot be reverted.\n";

        return false;
    }
    */
}
