<?php

use yii\db\Migration;

/**
 * Class m200513_122105_orangedataparams
 */
class m200513_122105_orangedataparams extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner','OrangeDataSingKey', $this->string(100));
        $this->addColumn('partner','OrangeDataConKey', $this->string(100));
        $this->addColumn('partner','OrangeDataConCert', $this->string(100));
        $this->addColumn('partner','IsUseKKmPrint', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner','OrangeDataSingKey');
        $this->dropColumn('partner','OrangeDataConKey');
        $this->dropColumn('partner','OrangeDataConCert');
        $this->dropColumn('partner','IsUseKKmPrint');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200513_122105_orangedataparams cannot be reverted.\n";

        return false;
    }
    */
}
