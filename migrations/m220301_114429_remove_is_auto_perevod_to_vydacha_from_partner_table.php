<?php

use yii\db\Migration;

/**
 * Class m220301_114429_remove_is_auto_perevod_to_vydacha_from_partner_table
 */
class m220301_114429_remove_is_auto_perevod_to_vydacha_from_partner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('partner', 'IsAutoPerevodToVydacha');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('partner', 'IsAutoPerevodToVydacha', $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->comment('avtoperevod na vydachy')
        );
    }
}
