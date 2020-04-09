<?php

use yii\db\Migration;

/**
 * Class m200213_122956_addbalancepartner
 */
class m200213_122956_addbalancepartner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'BalanceIn','bigint(19) NOT NULL DEFAULT \'0\' COMMENT \'balans pogashenia v kopeikah\'');
        $this->addColumn('partner', 'BalanceOut','bigint(19) NOT NULL DEFAULT \'0\' COMMENT \'balans vydachi v kopeikah\'');

        $this->alterColumn('partner_sumorder', 'Summ', 'bigint(19) NOT NULL DEFAULT \'0\' COMMENT \'summa\'');
        $this->alterColumn('partner_sumorder', 'SummAfter', 'bigint(19) NOT NULL DEFAULT \'0\' COMMENT \'summa balansa posle operacii\'');

        $this->execute('
            CREATE TABLE `partner_orderin` (
              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `IdPartner` int(10) unsigned NOT NULL COMMENT \'id partner\',
              `Comment` varchar(250) DEFAULT NULL COMMENT \'info\',
              `Summ` bigint(19) NOT NULL DEFAULT \'0\' COMMENT \'summa\',
              `DateOp` int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'data\',
              `TypeOrder` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'tip\',
              `SummAfter` bigint(19) NOT NULL DEFAULT \'0\' COMMENT \'summa balansin posle operacii\',
              `IdPay` int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'id pay_schet\',
              `IdStatm` int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'id statements_account\',
              PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8        
        ');

        $this->execute('
            CREATE TABLE `partner_orderout` (
              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `IdPartner` int(10) unsigned NOT NULL COMMENT \'id partner\',
              `Comment` varchar(250) DEFAULT NULL COMMENT \'info\',
              `Summ` bigint(19) NOT NULL DEFAULT \'0\' COMMENT \'summa\',
              `DateOp` int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'data\',
              `TypeOrder` tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'tip\',
              `SummAfter` bigint(19) NOT NULL DEFAULT \'0\' COMMENT \'summa balansout posle operacii\',
              `IdPay` int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'id pay_schet\',
              `IdStatm` int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'id statements_account\',
              PRIMARY KEY (`ID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8        
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'BalanceIn');
        $this->dropColumn('partner', 'BalanceOut');

        $this->dropTable('partner_orderin');
        $this->dropTable('partner_orderout');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200213_122956_addbalancepartner cannot be reverted.\n";

        return false;
    }
    */
}
