<?php

use yii\db\Migration;

/**
 * Class m200124_070007_changecomissparams
 */
class m200124_070007_changecomissparams extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('uslugatovar', ['ProvComisPC' => 0.7, 'ProvComisMin' => 40], 'IsCustom = 10');
        $this->update('uslugatovar', ['ProvComisPC' => 0.7, 'ProvComisMin' => 40], 'IsCustom = 12');
        $this->update('uslugatovar', ['ProvComisPC' => 2.0, 'ProvComisMin' => 0], 'IsCustom = 14');
        $this->update('uslugatovar', ['ProvComisPC' => 2.0, 'ProvComisMin' => 0], 'IsCustom = 16');
        $this->update('uslugatovar', ['ProvComisPC' => 0.35, 'ProvComisMin' => 40, 'ProvVoznagPC' => 0.5, 'ProvVoznagMin' => 45], 'IsCustom = 13');
        $this->update('uslugatovar', ['ProvComisPC' => 0.3, 'ProvComisMin' => 30, 'ProvVoznagPC' => 0.4, 'ProvVoznagMin' => 35], 'IsCustom = 11');

        $this->update('uslugatovar', ['ProvComisPC' => 0, 'ProvComisMin' => 25], 'IsCustom = 17');
        $this->update('uslugatovar', ['ProvComisPC' => 0, 'ProvComisMin' => 25], 'IsCustom = 19');
        $this->update('uslugatovar', ['ProvComisPC' => 0, 'ProvComisMin' => 0], 'IsCustom = 21');

        $this->execute('ALTER TABLE `pay_schet` DROP INDEX `IdOrg`, ADD INDEX `IdOrg` (`DateCreate`, `IdOrg`, `Extid`, `IdUsluga`)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200124_070007_changecomissparams cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200124_070007_changecomissparams cannot be reverted.\n";

        return false;
    }
    */
}
