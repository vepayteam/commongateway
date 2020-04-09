<?php

use yii\db\Migration;

/**
 * Class m191210_122542_tablelogbd
 */
class m191210_122542_tablelogbd extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE `loglogin` (
              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data vhoda',
              `IdUser` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id user',
              `Type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '1 - ok 2 - err, 3 - change pw',
              `IPLogin` varchar(30) NOT NULL DEFAULT '' COMMENT 'ip adres',
              `DopInfo` varchar(500) NOT NULL COMMENT 'info o brauzere',
              PRIMARY KEY (`ID`),
              KEY `IdUser` (`IdUser`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='логи входа'
        ");

        $this->execute("CREATE TABLE `key_log` (
              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data vhoda',
              `IdUser` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id user',
              `Type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '1 - ok 2 - err 3 - change pw 4 - set key1 5 - set key 2 6 - set key 3 7 - change keys 9 - exit',
              `IPLogin` varchar(30) NOT NULL DEFAULT '' COMMENT 'ip adres',
              `DopInfo` varchar(500) NOT NULL COMMENT 'info o brauzere',
              PRIMARY KEY (`ID`),
              KEY `IdUser` (`IdUser`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='логи ключей'
        ");

        $this->execute("
            CREATE TABLE `key_users` (
              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Login` varchar(20) NOT NULL COMMENT 'login',
              `Password` varchar(100) NOT NULL COMMENT 'pw sha2',
              `FIO` varchar(100) DEFAULT NULL COMMENT 'fio',
              `Email` varchar(50) DEFAULT NULL COMMENT 'email',
              `Key1Admin` TINYINT (1) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'admin vvoda klucha1',
              `Key2Admin` TINYINT (1) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'admin vvoda klucha2',
              `Key3Admin` TINYINT (1) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'admin vvoda klucha3',
              `KeyChageAdmin` TINYINT (1) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'admin zameny kychei',
              `DateChange` INT(10) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'data izmemenia',
              `AutoLockDate` INT(10) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'data avtoblokirovki',
              `DateLastLogin` INT(10) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'data poslednego vhoda',
              `IsActive` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - off 1 - on',
              `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
              PRIMARY KEY (`ID`),
              UNIQUE KEY `Login` (`Login`),
              KEY `IdPartner` (`IsDeleted`,`IsActive`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
        ");

        $this->execute("INSERT INTO `vepay`.`key_users` (`Login`, `Password`, `FIO`, `Key1Admin`, `Key2Admin`, `Key3Admin`, `KeyChageAdmin`) VALUES ('anton', '25f43b1486ad95a1398e3eeb3d83bc4010015fcc9bedb35b432e00298d5021f7', 'anton', '1', '1', '1', '1')");
        $this->execute("INSERT INTO `vepay`.`key_users` (`Login`, `Password`, `FIO`, `Key1Admin`, `Key2Admin`, `Key3Admin`, `KeyChageAdmin`) VALUES ('yana', '25f43b1486ad95a1398e3eeb3d83bc4010015fcc9bedb35b432e00298d5021f7', 'yana', '1', '1', '1', '1')");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('loglogin');
        $this->dropTable('key_log');
        $this->dropTable('key_users');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191210_122542_tablelogbd cannot be reverted.\n";

        return false;
    }
    */
}
