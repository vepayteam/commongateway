<?php

use yii\db\Migration;

/**
 * Class m200306_111112_actupdsum
 */
class m200306_111112_actupdsum extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE `act_mfo` 
              CHANGE `SumPerevod` `SumPerevod` BIGINT (20) UNSIGNED NULL COMMENT 'summa perevodov',
              CHANGE `ComisPerevod` `ComisPerevod` BIGINT (20) UNSIGNED NULL COMMENT 'komissia po perevodam',
              CHANGE `SumVozvrat` `SumVozvrat` BIGINT (20) UNSIGNED NULL COMMENT 'summa vozvrata perevodov',
              CHANGE `SumVyplata` `SumVyplata` BIGINT (20) UNSIGNED NULL COMMENT 'summa vyplat',
              CHANGE `ComisVyplata` `ComisVyplata` BIGINT (20) UNSIGNED NULL COMMENT 'komissia po vyplatam',
              CHANGE `BeginOstatokPerevod` `BeginOstatokPerevod` BIGINT (20) NULL COMMENT ' nachalnyii ostatok po perevodam',
              CHANGE `BeginOstatokVyplata` `BeginOstatokVyplata` BIGINT (20) NULL COMMENT 'nachalnyii ostatok po vyplate',
              CHANGE `EndOstatokPerevod` `EndOstatokPerevod` BIGINT (20) NULL COMMENT 'ostatok po perevodam',
              CHANGE `EndOstatokVyplata` `EndOstatokVyplata` BIGINT (20) NULL COMMENT 'ostatok po vyplate',
              CHANGE `SumPerechislen` `SumPerechislen` BIGINT (20) UNSIGNED NULL COMMENT 'perechsilennaya summa po perevodam',
              CHANGE `SumPostuplen` `SumPostuplen` BIGINT (20) UNSIGNED NULL COMMENT 'postupivshaya summa dlia vydachi',
              CHANGE `BeginOstatokVoznag` `BeginOstatokVoznag` BIGINT (20) DEFAULT 0 NULL,
              CHANGE `EndOstatokVoznag` `EndOstatokVoznag` BIGINT (20) DEFAULT 0 NULL,
              CHANGE `SumPodlejUderzOspariv` `SumPodlejUderzOspariv` BIGINT (20) DEFAULT 0 NULL,
              CHANGE `SumPodlejVozmeshOspariv` `SumPodlejVozmeshOspariv` BIGINT (20) DEFAULT 0 NULL,
              CHANGE `SumPerechKontrag` `SumPerechKontrag` BIGINT (20) DEFAULT 0 NULL,
              CHANGE `SumPerechObespech` `SumPerechObespech` BIGINT (20) DEFAULT 0 NULL ;         
        ");

        $this->execute("
            ALTER TABLE `act_mfo` 
              CHANGE `IdPartner` `IdPartner` INT (10) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'id partner mfo',
              CHANGE `ActPeriod` `ActPeriod` INT (10) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'period unixts',
              CHANGE `CntPerevod` `CntPerevod` INT (10) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'chislo perevodov',
              CHANGE `SumPerevod` `SumPerevod` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'summa perevodov',
              CHANGE `ComisPerevod` `ComisPerevod` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'komissia po perevodam',
              CHANGE `SumVozvrat` `SumVozvrat` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'summa vozvrata perevodov',
              CHANGE `CntVyplata` `CntVyplata` INT (10) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'chislo vyplat',
              CHANGE `SumVyplata` `SumVyplata` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'summa vyplat',
              CHANGE `ComisVyplata` `ComisVyplata` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'komissia po vyplatam',
              CHANGE `BeginOstatokPerevod` `BeginOstatokPerevod` BIGINT (20) DEFAULT 0 NOT NULL COMMENT ' nachalnyii ostatok po perevodam',
              CHANGE `BeginOstatokVyplata` `BeginOstatokVyplata` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'nachalnyii ostatok po vyplate',
              CHANGE `EndOstatokPerevod` `EndOstatokPerevod` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'ostatok po perevodam',
              CHANGE `EndOstatokVyplata` `EndOstatokVyplata` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'ostatok po vyplate',
              CHANGE `SumPerechislen` `SumPerechislen` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'perechsilennaya summa po perevodam',
              CHANGE `SumPostuplen` `SumPostuplen` BIGINT (20) DEFAULT 0 NOT NULL COMMENT 'postupivshaya summa dlia vydachi',
              CHANGE `BeginOstatokVoznag` `BeginOstatokVoznag` BIGINT (20) DEFAULT 0 NOT NULL,
              CHANGE `EndOstatokVoznag` `EndOstatokVoznag` BIGINT (20) DEFAULT 0 NOT NULL,
              CHANGE `SumPodlejUderzOspariv` `SumPodlejUderzOspariv` BIGINT (20) DEFAULT 0 NOT NULL,
              CHANGE `SumPodlejVozmeshOspariv` `SumPodlejVozmeshOspariv` BIGINT (20) DEFAULT 0 NOT NULL,
              CHANGE `SumPerechKontrag` `SumPerechKontrag` BIGINT (20) DEFAULT 0 NOT NULL,
              CHANGE `SumPerechObespech` `SumPerechObespech` BIGINT (20) DEFAULT 0 NOT NULL ;        
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200306_111112_actupdsum cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200306_111112_actupdsum cannot be reverted.\n";

        return false;
    }
    */
}
