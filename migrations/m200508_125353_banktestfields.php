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
        $this->addColumn('banks', 'LastWorkIn', $this->integer(10)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'LastInPay', $this->integer(10)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'LastInCheck', $this->integer(10)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'UsePayIn', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));

        $this->update('banks', ['UsePayIn' => 1], 'ID = 2');
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
        $this->dropColumn('banks', 'LastWorkIn');
        $this->dropColumn('banks', 'UseInPay');
        $this->dropColumn('banks', 'LastInCheck');
        $this->dropColumn('banks', 'LastPayIn');

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
