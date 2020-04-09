<?php

use yii\db\Migration;

/**
 * Class m191223_084726_addvoznagmin
 */
class m191223_084726_addvoznagmin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE `banks` 
                ADD COLUMN `OCTVoznMin` DOUBLE DEFAULT 0 NOT NULL AFTER `OCTVozn`, 
                ADD COLUMN `FreepayVoznMin` DOUBLE DEFAULT 0 NOT NULL AFTER `FreepayVozn`");
				
		$this->execute("CREATE TABLE `vyvod_system`(
            `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT, 
            `DateOp` INT UNSIGNED NOT NULL COMMENT 'data operacii', 
            `IdPartner` INT UNSIGNED NOT NULL COMMENT 'id partner', 
            `DateFrom` INT UNSIGNED NOT NULL COMMENT 'data s', 
            `DateTo` INT UNSIGNED NOT NULL COMMENT 'data po', 
            `Summ` INT UNSIGNED NOT NULL COMMENT 'summa v kop', 
            `SatateOp` TINYINT UNSIGNED NOT NULL COMMENT 'status - 1 - ispolneno 0 - v rabote 2 - ne ispolneno', 
            `IdPay` INT UNSIGNED NOT NULL COMMENT 'id pay_schet', 
            PRIMARY KEY (`ID`), 
            INDEX (`IdPartner`, `DateFrom`, `DateTo`, `SatateOp`)
        )");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('banks', 'OCTVoznMin');
        $this->dropColumn('banks', 'FreepayVoznMin');
        $this->dropTable('vyvod_system');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191223_084726_addvoznagmin cannot be reverted.\n";

        return false;
    }
    */
}
