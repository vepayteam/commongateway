<?php

use yii\db\Migration;

/**
 * Class m200401_080737_addpayschetvoznaghist
 */
class m200401_080737_addpayschetvoznaghist extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet', 'MerchVozn', $this
            ->bigInteger(19)
            ->notNull()
            ->defaultValue(0)
            ->comment('komissia vepay, kop')
            ->after('ComissSumm')
        );

        $this->addColumn('pay_schet', 'BankComis', $this
            ->bigInteger(19)
            ->notNull()
            ->defaultValue(0)
            ->comment('komissia banka, kop')
            ->after('MerchVozn')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet', 'MerchVozn');
        $this->dropColumn('pay_schet', 'BankComis');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200401_080737_addpayschetvoznaghist cannot be reverted.\n";

        return false;
    }
    */
}
