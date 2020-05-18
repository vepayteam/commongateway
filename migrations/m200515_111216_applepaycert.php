<?php

use yii\db\Migration;

/**
 * Class m200515_111216_applepaycert
 */
class m200515_111216_applepaycert extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner','Apple_MerchIdentKey', $this->string(100));
        $this->addColumn('partner','Apple_MerchIdentCert', $this->string(100));
        $this->addColumn('partner','Apple_displayName', $this->string(100));
        $this->addColumn('partner','IsUseApplepay', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner','Apple_MerchIdentKey');
        $this->dropColumn('partner','Apple_MerchIdentCert');
        $this->dropColumn('partner','Apple_displayName');
        $this->dropColumn('partner','IsUseApplepay');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200515_111216_applepaycert cannot be reverted.\n";

        return false;
    }
    */
}
