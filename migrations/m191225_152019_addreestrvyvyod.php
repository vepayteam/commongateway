<?php

use yii\db\Migration;

/**
 * Class m191225_152019_addreestrvyvyod
 */
class m191225_152019_addreestrvyvyod extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            CREATE TABLE `vyvod_reestr` (
              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `IdPartner` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id partners',
              `DateFrom` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data c',
              `DateTo` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data po',
              `DateOp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data operacii',
              `SumOp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'summa',
              `StateOp` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'status - 0 - v obrabotke 1 - ispolnena 2 - otmeneno',
              `IdPay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id pay_schet',
              PRIMARY KEY (`ID`),
              KEY `IdPartner` (`IdPartner`,`DateFrom`,`DateTo`),
              KEY `IdPay` (`IdPay`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('vyvod_reestr');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191225_152019_addreestrvyvyod cannot be reverted.\n";

        return false;
    }
    */
}
