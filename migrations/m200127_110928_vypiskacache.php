<?php

use yii\db\Migration;

/**
 * Class m200127_110928_vypiskacache
 */
class m200127_110928_vypiskacache extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            CREATE TABLE `statements_account` (
                `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `IdPartner` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id partner',
                `TypeAccount` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip schet partnera - 0 - vydacha 1 - pogashenie 2 - nominalnyii',
                `BnkId` bigint(20) unsigned DEFAULT '0' COMMENT 'id',
                `NumberPP` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'number',
                `DatePP` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data',
                `SummPP` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'summa',
                `SummComis` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'komissia vepay',
                `Description` varchar(250) DEFAULT NULL COMMENT 'naznachenie',
                `IsCredit` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - spisanie, 1 - popolnenie',
                `Name` varchar(250) DEFAULT NULL COMMENT 'kontragent',
                `Inn` varchar(50) DEFAULT NULL COMMENT 'inn',
                `Account` varchar(50) DEFAULT NULL COMMENT 'rsch.schet',
                `Bic` varchar(50) DEFAULT NULL COMMENT 'bik banka',
                `Bank` varchar(250) DEFAULT NULL COMMENT 'bank',
                `BankAccount` varchar(50) DEFAULT NULL COMMENT 'kor.schet',
                PRIMARY KEY (`ID`),
                KEY `DatePP` (`DatePP`,`IdPartner`),
                KEY `BnkId` (`BnkId`,`IdPartner`,`TypeAccount`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $this->execute("
            CREATE TABLE `statements_planner` (
                `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `IdPartner` int(10) unsigned NOT NULL DEFAULT '0',
                `IdTypeAcc` int(10) unsigned NOT NULL DEFAULT '0',
                `DateUpdateFrom` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data nachala vypiski',
                `DateUpdateTo` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data obnovlenia vypiski',
                PRIMARY KEY (`ID`),
                KEY `IdPartner` (`IdPartner`,`IdTypeAcc`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('statements_account');
        $this->dropTable('statements_planner');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200127_110928_vypiskacache cannot be reverted.\n";

        return false;
    }
    */
}
