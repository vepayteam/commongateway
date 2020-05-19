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
        $this->addColumn('Banks', 'UseApplePay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('Banks', 'UseGooglePay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('Banks', 'UseSamsungPay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
        $this->addColumn('Banks', 'SortOrder', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('Banks', 'UseApplePay');
        $this->dropColumn('Banks', 'UseGooglePay');
        $this->dropColumn('Banks', 'UseSamsungPay');
        $this->dropColumn('Banks', 'SortOrder');

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
