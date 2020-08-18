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

        $this->addColumn('partner','MtsLoginAft', $this->string(100));
        $this->addColumn('partner','MtsPasswordAft', $this->string(100));
        $this->addColumn('partner','MtsTokenAft', $this->string(100));

        $this->addColumn('partner','MtsLoginJkh', $this->string(100));
        $this->addColumn('partner','MtsPasswordJkh', $this->string(100));
        $this->addColumn('partner','MtsTokenJkh', $this->string(100));

        $this->addColumn('partner','MtsLoginOct', $this->string(100));
        $this->addColumn('partner','MtsPasswordOct', $this->string(100));
        $this->addColumn('partner','MtsTokenOct', $this->string(100));

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

        $this->dropColumn('partner','MtsLoginAft');
        $this->dropColumn('partner','MtsPasswordAft');
        $this->dropColumn('partner','MtsTokenAft');

        $this->dropColumn('partner','MtsLoginJkh');
        $this->dropColumn('partner','MtsPasswordJkh');
        $this->dropColumn('partner','MtsTokenJkh');

        $this->dropColumn('partner','MtsLoginOct');
        $this->dropColumn('partner','MtsPasswordOct');
        $this->dropColumn('partner','MtsTokenOct');

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
