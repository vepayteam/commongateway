<?php

use yii\db\Migration;

/**
 * Class m200219_130442_actmfoupd
 */
class m200219_130442_actmfoupd extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('act_mfo', 'SumPerevod', 'bigint(20) unsigned NOT NULL COMMENT \'summa perevodov\'');
        $this->alterColumn('act_mfo', 'ComisPerevod', 'bigint(20) unsigned NOT NULL COMMENT \'komissia po perevodam\'');

        $this->addColumn('act_mfo', 'SumVozvrat', 'bigint(20) unsigned NOT NULL COMMENT \'summa vozvrata perevodov\'');

        $this->addColumn('act_mfo', 'CntVyplata', 'int(10) unsigned NOT NULL COMMENT \'chislo vyplat\'');
        $this->addColumn('act_mfo', 'SumVyplata', 'bigint(20) unsigned NOT NULL COMMENT \'summa vyplat\'');
        $this->addColumn('act_mfo', 'ComisVyplata', 'bigint(20) unsigned NOT NULL COMMENT \'komissia po vyplatam\'');

        $this->addColumn('act_mfo', 'BeginOstatokPerevod', 'bigint(20) NOT NULL COMMENT \' nachalnyii ostatok po perevodam\'');
        $this->addColumn('act_mfo', 'BeginOstatokVyplata', 'bigint(20) NOT NULL COMMENT \'nachalnyii ostatok po vyplate\'');

        $this->addColumn('act_mfo', 'EndOstatokPerevod', 'bigint(20) NOT NULL COMMENT \'ostatok po perevodam\'');
        $this->addColumn('act_mfo', 'EndOstatokVyplata', 'bigint(20) NOT NULL COMMENT \'ostatok po vyplate\'');

        $this->addColumn('act_mfo', 'SumPerechislen', 'bigint(20) unsigned NOT NULL COMMENT \'perechsilennaya summa po perevodam\'');
        $this->addColumn('act_mfo', 'SumPostuplen', 'bigint(20) unsigned NOT NULL COMMENT \'postupivshaya summa dlia vydachi\'');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('act_mfo', 'SumVozvrat');
        $this->dropColumn('act_mfo', 'CntVyplata');
        $this->dropColumn('act_mfo', 'SumVyplata');
        $this->dropColumn('act_mfo', 'ComisVyplata');
        $this->dropColumn('act_mfo', 'BeginOstatokPerevod');
        $this->dropColumn('act_mfo', 'BeginOstatokVyplata');
        $this->dropColumn('act_mfo', 'EndOstatokPerevod');
        $this->dropColumn('act_mfo', 'EndOstatokVyplata');
        $this->dropColumn('act_mfo', 'SumPerechislen');
        $this->dropColumn('act_mfo', 'SumPostuplen');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200219_130442_actmfoupd cannot be reverted.\n";

        return false;
    }
    */
}
