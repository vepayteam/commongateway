<?php

use yii\db\Migration;

/**
 * Class m200217_122932_addtypevyvodvozn
 */
class m200217_122932_addtypevyvodvozn extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('vyvod_system', 'TypeVyvod',
            'tinyint(1) unsigned NOT NULL DEFAULT \'0\' COMMENT \'tip - 0 - pogashenie 1 - vyplaty\'');
        $this->execute('ALTER TABLE `vyvod_system` DROP INDEX `IdPartner`, ADD INDEX `IdPartner` (`IdPartner`, `DateFrom`, `DateTo`, `SatateOp`, `TypeVyvod`)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE `vyvod_system` DROP INDEX `IdPartner`, ADD INDEX `IdPartner` (`IdPartner`, `DateFrom`, `DateTo`, `SatateOp`)');
        $this->dropColumn('vyvod_system', 'TypeVyvod');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200217_122932_addtypevyvodvozn cannot be reverted.\n";

        return false;
    }
    */
}
