<?php

use yii\db\Migration;

/**
 * Class m200310_095803_devidevyplata
 */
class m200310_095803_devidevyplata extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('vyvod_reestr','TypePerechisl', $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(1)
            ->comment('0 - perevod na vydachu 1 - perechislene na schet')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('vyvod_reestr','TypePerechisl');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200310_095803_devidevyplata cannot be reverted.\n";

        return false;
    }
    */
}
