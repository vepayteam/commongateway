<?php

use yii\db\Migration;

/**
 * Class m200123_120015_addperevodcomisgate
 */
class m200123_120015_addperevodcomisgate extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'LoginTkbPerevod', 'varchar(40) DEFAULT NULL');
        $this->addColumn('partner', 'KeyTkbPerevod', 'varchar(300) DEFAULT NULL');
        $this->addColumn('partner', 'IsUnreserveComis', 'tinyint(1) unsigned NOT NULL DEFAULT \'0\'');
        $this->addColumn('partner', 'SchetTCBUnreserve', 'varchar(40) DEFAULT NULL');

        $this->execute("
            CREATE TABLE `vozvr_comis` (
              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `IdPartner` int(10) unsigned NOT NULL DEFAULT '0',
              `DateFrom` int(10) unsigned NOT NULL DEFAULT '0',
              `DateTo` int(10) unsigned NOT NULL DEFAULT '0',
              `DateOp` int(10) unsigned NOT NULL DEFAULT '0',
              `SumOp` int(10) unsigned NOT NULL DEFAULT '0',
              `StateOp` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `IdPay` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`ID`),
              KEY `IdPartner` (`IdPartner`,`DateFrom`,`DateTo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8        
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'LoginTkbPerevod');
        $this->dropColumn('partner', 'KeyTkbPerevod');
        $this->dropColumn('partner', 'IsUnreserveComis');
        $this->dropColumn('partner', 'SchetTCBUnreserve');

        $this->dropTable('vozvr_comis');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200123_120015_addperevodcomisgate cannot be reverted.\n";

        return false;
    }
    */
}
