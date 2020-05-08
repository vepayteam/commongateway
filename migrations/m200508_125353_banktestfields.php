<?php

use yii\db\Migration;

/**
 * Class m200508_125353_banktestfields
 */
class m200508_125353_banktestfields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('banks', 'LastWork', $this->integer(10)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'LastPay', $this->integer(10)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'LastCheck', $this->integer(10)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'UsePay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));

        $this->insert('banks', [
            'ID' => 3,
            'Name' => 'МТС Банк',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('banks', 'LastWork');
        $this->dropColumn('banks', 'UsePay');
        $this->dropColumn('banks', 'LastCheck');
        $this->dropColumn('banks', 'LastPay');

        $this->delete('banks', 'ID = 3');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200508_125353_banktestfields cannot be reverted.\n";

        return false;
    }
    */
}
