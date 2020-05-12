<?php

use yii\db\Migration;

/**
 * Class m200512_083554_applepay
 */
class m200512_083554_applepay extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'Apple_MerchantID', $this->string(100));
        $this->addColumn('partner', 'Apple_PayProcCert', $this->text());
        $this->addColumn('partner', 'Apple_KeyPasswd', $this->string(100));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'Apple_MerchantID');
        $this->dropColumn('partner', 'Apple_PayProcCert');
        $this->dropColumn('partner', 'Apple_KeyPasswd');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200512_083554_applepay cannot be reverted.\n";

        return false;
    }
    */
}
