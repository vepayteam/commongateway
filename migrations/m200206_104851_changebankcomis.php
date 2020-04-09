<?php

use yii\db\Migration;

/**
 * Class m200206_104851_changebankcomis
 */
class m200206_104851_changebankcomis extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //ATF
        $this->update('uslugatovar', ['ProvComisPC' => 0.5, 'ProvComisMin' => 25], 'IsCustom = 10');
        //ECOM
        $this->update('uslugatovar', ['ProvComisPC' => 1.85, 'ProvComisMin' => 0], 'IsCustom = 14');
        $this->update('uslugatovar', ['ProvComisPC' => 1.85, 'ProvComisMin' => 0], 'IsCustom = 1');
        $this->update('uslugatovar', ['ProvComisPC' => 1.85, 'ProvComisMin' => 0], 'IsCustom = 2');
        //AVTOPLAT
        $this->update('uslugatovar', ['ProvComisPC' => 2.0, 'ProvComisMin' => 0.6], 'IsCustom = 16');
        //OCT
        $this->update('uslugatovar', ['ProvComisPC' => 0.25, 'ProvComisMin' => 25], 'IsCustom = 13');
        //PSR
        $this->update('uslugatovar', ['ProvComisPC' => 0.2, 'ProvComisMin' => 25], 'IsCustom = 11');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200206_104851_changebankcomis cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200206_104851_changebankcomis cannot be reverted.\n";

        return false;
    }
    */
}
