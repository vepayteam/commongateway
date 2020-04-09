<?php

use yii\db\Migration;

/**
 * Class m200313_074651_actschet2
 */
class m200313_074651_actschet2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('act_schet');
        $this->execute('
            CREATE TABLE `act_schet` (
              `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `IdPartner` int(10) unsigned NOT NULL,
              `IdAct` int(10) unsigned NOT NULL DEFAULT \'0\',
              `NumSchet` int(10) unsigned NOT NULL DEFAULT \'0\',
              `SumSchet` bigint(20) NOT NULL DEFAULT \'0\',
              `DateSchet` int(10) unsigned NOT NULL DEFAULT \'0\',
              `Komment` varchar(255) DEFAULT NULL,
              `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
              PRIMARY KEY (`ID`),
              KEY `IdPartner_idx` (`IdPartner`),
              KEY `IdAct_idx` (`IdAct`),
              KEY `DateSchet_idx` (`DateSchet`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8        
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200313_074651_actschet2 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200313_074651_actschet2 cannot be reverted.\n";

        return false;
    }
    */
}
