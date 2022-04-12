<?php

use yii\db\Migration;

/**
 * Class m200602_063231_googlepayconfig
 */
class m200602_063231_googlepayconfig extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner','GoogleMerchantID', $this->string(100));
        $this->addColumn('partner','IsUseGooglepay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));

        $this->addColumn('partner','SamsungMerchantID', $this->string(100));
        $this->addColumn('partner','IsUseSamsungpay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner','GoogleMerchantID');
        $this->dropColumn('partner','IsUseGooglepay');

        $this->dropColumn('partner','SamsungMerchantID');
        $this->dropColumn('partner','IsUseSamsungpay');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200602_063231_googlepayconfig cannot be reverted.\n";

        return false;
    }
    */
}
