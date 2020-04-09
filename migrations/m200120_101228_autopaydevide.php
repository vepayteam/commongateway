<?php

use yii\db\Migration;

/**
 * Class m200120_101228_autopaydevide
 */
class m200120_101228_autopaydevide extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'LoginTkbAuto1', 'varchar(40) DEFAULT NULL');
        $this->addColumn('partner', 'LoginTkbAuto2', 'varchar(40) DEFAULT NULL');
        $this->addColumn('partner', 'LoginTkbAuto3', 'varchar(40) DEFAULT NULL');
        $this->addColumn('partner', 'LoginTkbAuto4', 'varchar(40) DEFAULT NULL');
        $this->addColumn('partner', 'LoginTkbAuto5', 'varchar(40) DEFAULT NULL');
        $this->addColumn('partner', 'LoginTkbAuto6', 'varchar(40) DEFAULT NULL');
        $this->addColumn('partner', 'LoginTkbAuto7', 'varchar(40) DEFAULT NULL');

        $this->addColumn('partner', 'KeyTkbAuto1', 'varchar(300) DEFAULT NULL');
        $this->addColumn('partner', 'KeyTkbAuto2', 'varchar(300) DEFAULT NULL');
        $this->addColumn('partner', 'KeyTkbAuto3', 'varchar(300) DEFAULT NULL');
        $this->addColumn('partner', 'KeyTkbAuto4', 'varchar(300) DEFAULT NULL');
        $this->addColumn('partner', 'KeyTkbAuto5', 'varchar(300) DEFAULT NULL');
        $this->addColumn('partner', 'KeyTkbAuto6', 'varchar(300) DEFAULT NULL');
        $this->addColumn('partner', 'KeyTkbAuto7', 'varchar(300) DEFAULT NULL');

        $this->addColumn('partner', 'SchetTcbNominal', 'varchar(40) DEFAULT NULL AFTER `SchetTcbTransit`');

        $this->addColumn('pay_schet', 'AutoPayIdGate', 'tinyint(1) unsigned NOT NULL DEFAULT \'0\' AFTER `IsAutoPay`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'LoginTkbAuto1');
        $this->dropColumn('partner', 'LoginTkbAuto2');
        $this->dropColumn('partner', 'LoginTkbAuto3');
        $this->dropColumn('partner', 'LoginTkbAuto4');
        $this->dropColumn('partner', 'LoginTkbAuto5');
        $this->dropColumn('partner', 'LoginTkbAuto6');
        $this->dropColumn('partner', 'LoginTkbAuto7');

        $this->dropColumn('partner', 'KeyTkbAuto1');
        $this->dropColumn('partner', 'KeyTkbAuto2');
        $this->dropColumn('partner', 'KeyTkbAuto3');
        $this->dropColumn('partner', 'KeyTkbAuto4');
        $this->dropColumn('partner', 'KeyTkbAuto5');
        $this->dropColumn('partner', 'KeyTkbAuto6');
        $this->dropColumn('partner', 'KeyTkbAuto7');

        $this->dropColumn('partner', 'SchetTcbNominal');

        $this->dropColumn('pay_schet', 'AutoPayIdGate');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200120_101228_autopaydevide cannot be reverted.\n";

        return false;
    }
    */
}
