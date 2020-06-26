<?php

use app\models\bank\Banks;
use app\models\payonline\Partner;
use yii\db\Migration;

/**
 * Class m200619_064027_add_bank_for_payment_id_in_partner
 */
class m200619_064027_add_bank_for_payment_id_in_partner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Partner::tableName(),'BankForPaymentId', $this->integer()->defaultValue(-1));

        $option = new \app\models\Options();
        $option->Name = Banks::BANK_BY_PAYMENT_OPTION_NAME;
        $option->Value = '0';
        $option->save();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Partner::tableName(),'BankForPaymentId');

        \app\models\Options::deleteAll(['Name' => Banks::BANK_BY_PAYMENT_OPTION_NAME]);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200619_064027_add_bank_for_payment_id_in_partner cannot be reverted.\n";

        return false;
    }
    */
}
