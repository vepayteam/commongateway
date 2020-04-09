<?php

use yii\db\Migration;

/**
 * Class m200408_140014_adddogovor
 */
class m200408_140014_adddogovor extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet', 'Dogovor', $this->string());
        $this->addColumn('pay_schet', 'FIO', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet', 'Dogovor');
        $this->dropColumn('pay_schet', 'FIO');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200408_140014_adddogovor cannot be reverted.\n";

        return false;
    }
    */
}
