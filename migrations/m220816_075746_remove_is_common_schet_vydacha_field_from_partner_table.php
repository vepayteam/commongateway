<?php

use yii\db\Migration;

/**
 * Class m220816_075746_remove_is_common_schet_vydacha_field_from_partner_table
 */
class m220816_075746_remove_is_common_schet_vydacha_field_from_partner_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('partner', 'IsCommonSchetVydacha');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $column = $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(0);

        $this->addColumn('partner', 'IsCommonSchetVydacha', $column);
    }
}
