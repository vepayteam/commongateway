<?php

use yii\db\Migration;

/**
 * Class m200519_092023_bankorder
 */
class m200519_092023_bankorder extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('banks', 'UseApplePay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'UseGooglePay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'UseSamsungPay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('banks', 'SortOrder', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('banks', 'UseApplePay');
        $this->dropColumn('banks', 'UseGooglePay');
        $this->dropColumn('banks', 'UseSamsungPay');
        $this->dropColumn('banks', 'SortOrder');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200519_092023_bankorder cannot be reverted.\n";

        return false;
    }
    */
}
